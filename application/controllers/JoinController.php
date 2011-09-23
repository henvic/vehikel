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
        
        $signUp = Ml_Model_SignUp::getInstance();
        
        $form = $signUp->signUpForm();
        
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $data = $form->getValues();
            
            if (isset($data['invitecode'])) {
                $inviteCode = $data['invitecode'];
            } else {
                $inviteCode = false;
            }
            
            $newUserInfo = $signUp->newUser($data['name'], $data['email'], $inviteCode);
            
            $this->view->entry = $newUserInfo;
            
            $mail = new Zend_Mail();
            
            $mail->setBodyText($this->view->render("join/email.phtml"))
                ->setFrom($config['robotEmail']['addr'], $config['robotEmail']['name'])
                ->addTo($data['email'], $data['name'])
                ->setSubject('Your new ' . $config['applicationname'] . ' account')
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
        
        $signUp = Ml_Model_SignUp::getInstance();
        $credential = Ml_Model_Credential::getInstance();
        $people = Ml_Model_People::getInstance();
        $profile = Ml_Model_Profile::getInstance();
        
        if ($config['ssl'] && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on")) {
            $this->_redirect("https://" . $config['webhostssl'] .
              $router->assemble(array($request->getUserParams()), 
              "join_emailconfirm"), array("exit"));
        }
        
        $securityCode = $request->getParam("security_code");
        
        $confirmationInfo = $signUp->getByHash($securityCode);
        
        if (! $confirmationInfo) {
            $this->getResponse()->setHttpResponseCode(404);
            return $this->_forward("unavailable");
        }
        
        $form = $signUp->newIdentityForm($securityCode);
           
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $newUsername = $form->getValue("newusername");
            $password = $form->getValue("password");
            
            $preUserInfo = (array(
            "alias" => $newUsername, 
            "membershipdate" => $confirmationInfo['timestamp'], 
            "name" => $confirmationInfo['name'], 
            "email" => $confirmationInfo['email']));
            
            $uid = $people
            ->create($newUsername, $password, $preUserInfo, $confirmationInfo);
            
            $getUserByUsername = $people->getByUsername($preUserInfo['alias']);
            
            $adapter = $credential
            ->getAuthAdapter($getUserByUsername['id'], $password);
            
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
