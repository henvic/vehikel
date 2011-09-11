<?php
/**
 * Sign Up
*/

class JoinController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $auth = Zend_Auth::getInstance();
        $registry = Zend_Registry::getInstance();
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $config = $registry->get('config');
        
        $request = $this->getRequest();
        
        if ($auth->hasIdentity()) {
            $this->_redirect($router->assemble(array(), "logout") . "?please", array("exit"));
        }
        
        $signUp = Ml_Signup::getInstance();
        
        $form = $signUp->signUpForm();
        
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $data = $form->getValues();
            
            $signUp->getAdapter()->beginTransaction();
            
            try {
                $newUserInfo = $signUp->newUser($data['name'], $data['email']);
                
                if (isset($data['invitecode']) && ! empty($data['invitecode']) &&
                 ! $registry->isRegistered("inviteCompleteBefore") &&
                 ! $registry->isRegistered("inviteMultiple")) {
                    $invites = new Ml_Invites();
                    $invites->update(array("used" => "1"),
                     $invites->getAdapter()
                     ->quoteInto("hash = ?", $data['invitecode']));
                }
                
                $signUp->getAdapter()->commit();
            } catch(Exception $e)
            {
                $signUp->getAdapter()->rollBack();
                throw $e;
            }
            
            
            $this->view->entry = $newUserInfo;
            
            $mail = new Zend_Mail();
            
            $mail->setBodyText($this->view->render("join/email.phtml"))
                ->setFrom($config['robotEmail']['addr'], $config['robotEmail']['name'])
                ->addTo($data['email'], $data['name'])
                ->setSubject('Your new '.$config['applicationname'].' account')
                ->send();
            
            $this->view->success = true;
        } else {
            $this->view->signUpForm = $form;
        }
    }
    
    public function unavailableAction()
    {
    }
    
    public function confirmAction()
    {
        $auth = Zend_Auth::getInstance();
        
        $request = $this->getRequest();
        
        $registry = Zend_Registry::getInstance();
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $config = $registry->get("config");
        
        if ($auth->hasIdentity()) {
            $registry->set("pleaseSignout", true);
            return $this->_forward("index", "logout");
        }
        
        $newUser = Ml_Signup::getInstance();
        $credential = Ml_Credential::getInstance();
        $people = Ml_People::getInstance();
        $profile = new Ml_Profile();
        
        if ($config['ssl'] && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on")) {
            $this->_redirect("https://" . $config['webhostssl'] .
              $router->assemble(array($request->getUserParams()), 
              "join_emailconfirm"), array("exit"));
        }
        
        $select = $newUser->select();
        $securityCode = $request->getParam("security_code");
        
        $select
        ->where('securitycode = ?', $securityCode)
        ->where('timestamp >= ?', date("Y-m-d H:i:s", time()-(48*60*60)));
        
        $confirmationInfo = $newUser->fetchRow($select);
        
        if (! is_object($confirmationInfo)) {
            $this->getResponse()->setHttpResponseCode(404);
            return $this->_forward("unavailable");
        }
        
        $confirmationInfo = $confirmationInfo->toArray();
        
           $form = $newUser->newIdentityForm($securityCode);
           
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $newUser->getAdapter()->beginTransaction();
            
            try {
                $newUser->delete($newUser->getAdapter()->quoteInto('id = ?', $confirmationInfo['id']));
                
                  $preUserInfo = (array(
                        "alias"          => $form->getValue("newusername"),
                        "membershipdate" => $confirmationInfo['timestamp'],
                        "name"           => $confirmationInfo['name'],
                        "email"          => $confirmationInfo['email'],
                  ));
                   
                   $people->insert($preUserInfo);
            
                   $uid = $people->getAdapter()->lastInsertId();
                   if (! $uid) {
                       throw new Exception("No ID.");
                   }
                   
                   $credential->setCredential($uid, $form->getValue("password"));
                   
                   $profile->insert(array("id" => $uid));
                   
                   $newUser->getAdapter()->commit();
            } catch(Exception $e)
            {
                $newUser->getAdapter()->rollBack();
                throw $e;
            }
            
            $getUserByUsername = $people->getByUsername($preUserInfo['alias']);
            
            $adapter = $credential->getAuthAdapter($getUserByUsername['id'], $form->getValue("password"));
            
            if ($adapter) {
                $result  = $auth->authenticate($adapter);
                if ($result->getCode() != Zend_Auth_Result::SUCCESS) {
                    throw new Exception("Could not authenticate 'just created' user");
                }
            }
            
            Zend_Session::regenerateId();
            
            $this->_redirect($router->assemble(array(), "join_welcome"), array("exit"));
        }
        
        $this->view->entry = $confirmationInfo;
        $this->view->confirmForm = $form;
    }
    
    public function welcomeAction()
    {
        $auth = Zend_Auth::getInstance();
        
        if (! $auth->hasIdentity()) {
            Zend_Controller_Front::getInstance()
            ->registerPlugin(new Ml_Plugins_LoginRedirect());
        }
        
        $this->view->joined = true;
    }
}
