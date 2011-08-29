<?php
/**
 * Sign Up
*/

class JoinController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $request = $this->getRequest();
        
        $auth = Zend_Auth::getInstance();
        $registry = Zend_Registry::getInstance();
        
        $config = $registry->get('config');
        
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $this->_redirect(Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), "logout") . "?please", array("exit"));
        }
        
        $signUp = ML_Signup::getInstance();
        
        $form = $signUp->_getSignUpForm();
        
        if ($request->isPost() && $form->isValid($request->getPost()))
        {
            $data = $form->getValues();
            
            $signUp->getAdapter()->beginTransaction();
            
            try {
                $newUserInfo = $signUp->newUser($data['name'], $data['email']);
                
                if (isset($data['invitecode']) && !empty($data['invitecode']) && !$registry->isRegistered("inviteCompleteBefore") && !$registry->isRegistered("inviteMultiple"))
                {
                    $Invites = new ML_Invites();
                    $Invites->update(array("used" => "1"), $Invites->getAdapter()->quoteInto("hash = ?", $data['invitecode']));
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
        $request = $this->getRequest();
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $registry->set("pleaseSignout", true);
            return $this->_forward("index", "logout");
        }
        
        
        $newUser = ML_Signup::getInstance();
        $Credential = ML_Credential::getInstance();
        $People = ML_People::getInstance();
        $Profile = new ML_Profile();
        
        
        if ($config['ssl'] && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on")) {
            $this->_redirect("https://" . $config['webhostssl'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array($request->getUserParams()), "join_emailconfirm"), array("exit"));
        }
        
        $select = $newUser->select();
        $security_code = $request->getParam("security_code");
        $select
               ->where('securitycode = ?', $security_code)
               ->where('timestamp >= ?', date("Y-m-d H:i:s", time()-(48*60*60)));
        
        $confirmationInfo = $newUser->fetchRow($select);
        
        if (!is_object($confirmationInfo)) {
            $this->getResponse()->setHttpResponseCode(404);
            return $this->_forward("unavailable");
        }
        
        $confirmationInfo = $confirmationInfo->toArray();
        
           $form = $newUser->_getIdentityForm($security_code);
           
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $newUser->getAdapter()->beginTransaction();
            
            try {
                $newUser->delete($newUser->getAdapter()->quoteInto('id = ?', $confirmationInfo['id']));
                
                  $pre_userInfo = (array(
                        "alias"          => $form->getValue("newusername"),
                        "membershipdate" => $confirmationInfo['timestamp'],
                        "name"           => $confirmationInfo['name'],
                        "email"          => $confirmationInfo['email'],
                  ));
                   
                   $People->insert($pre_userInfo);
            
                   $uid = $People->getAdapter()->lastInsertId();
                   if (!$uid) {
                       throw new Exception("No ID.");
                   }
                   
                   $Credential->setCredential($uid, $form->getValue("password"));
                   
                   $Profile->insert(array("id" => $uid));
                   
                   $newUser->getAdapter()->commit();
            } catch(Exception $e)
            {
                $newUser->getAdapter()->rollBack();
                throw $e;
            }
            
            $getUserByUsername = $People->getByUsername($pre_userInfo['alias']);
            
            $adapter = $Credential->getAuthAdapter($getUserByUsername['id'], $form->getValue("password"));
            
            if ($adapter) {
                $auth    = Zend_Auth::getInstance();
                $result  = $auth->authenticate($adapter);
                
                if ($result->getCode() != Zend_Auth_Result::SUCCESS)
                {
                    throw new Exception("Could not authenticate 'just created' user");
                }
            }
            
            Zend_Session::regenerateId();
            
            $this->_redirect(Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), "join_welcome"), array("exit"));
        }
        
        $this->view->entry = $confirmationInfo;
        $this->view->confirmForm = $form;
    }
    
    public function welcomeAction()
    {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            Zend_Controller_Front::getInstance()->registerPlugin(new ML_Plugins_LoginRedirect());
        }
        
        //$this->view->alias = $form->getValue("newusername");
        $this->view->joined = true;
    }
}
