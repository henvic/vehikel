<?php
class PasswordController extends Zend_Controller_Action
{
    public function unavailableAction()
    {
        $this->getResponse()->setHttpResponseCode(404);
    }
    
    public function recoverAction()
    {
        $request = $this->getRequest();
        
        $registry = Zend_Registry::getInstance();
        $auth = Zend_Auth::getInstance();
        
        $config = $registry->get('config');
        
        if ($auth->hasIdentity()) {
            $registry->set("pleaseSignout", true);
            return $this->_forward("index", "logout");
        }
        
        $people = Ml_Model_People::getInstance();
        $recover = Ml_Model_Recover::getInstance();
        
        $form = $recover->form();
        
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $find = $form->getValues();
            
            //AccountRecover.php validator pass this data: not very hortodox
            $getUser = $registry->accountRecover;
            
            $securityCode = $recover->newCase($getUser['id']);
            
            $this->view->securitycode = $securityCode;
            $this->view->recoverUser = $getUser;
            
            $this->view->recovering = true;
            
            $mail = new Zend_Mail();
            
            $mail
            ->setBodyText($this->view->render("password/emailRecover.phtml"))
            ->setFrom($config['robotEmail']['addr'], $config['robotEmail']['name'])
            ->addTo($getUser['email'], $getUser['name'])
            ->setSubject('Recover your '.$config['applicationname'].' account')
            ->send();
        }
        
        $this->view->recoverForm = $form;
    }
    
    public function passwordAction()
    {
        $request = $this->getRequest();
        
        $auth = Zend_Auth::getInstance();
        $registry = Zend_Registry::getInstance();
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $people = Ml_Model_People::getInstance();
        $credential = Ml_Model_Credential::getInstance();
        $recover = Ml_Model_Recover::getInstance();
        
        $params = $request->getParams();
        
        $this->view->request = $request;
        
        if ($auth->hasIdentity()) {
            if (isset($params['confirm_uid'])) {
                $this->_redirect($router->assemble(array(), "logout") . "?please", array("exit"));
            }
            
            $form = $credential->newPasswordForm();
            $uid = $auth->getIdentity();
            $registry->set("changeUserProperPassword", true);
            
            $signedUserInfo = $registry->get("signedUserInfo");
            
        } else {
            if (isset($params['confirm_uid']) && isset($params['security_code'])) {
                $recoverInfo = $recover
                ->getAuthorization($params["confirm_uid"], $params["security_code"]);
                
                if (! $recoverInfo) {
                    return $this->_forward("unavailable");
                }
                
                $form = $credential
                ->newPasswordForm($params["confirm_uid"], $params["security_code"]);
                
                $uid = $recoverInfo['uid'];
            } else {
                return $this->_forward("redirect", "login");
            }
        }
        
        if ($auth->hasIdentity()) {
            $this->view->userInfoDataForPasswordChange = $signedUserInfo;
        } else {
            $userInfo = $people->getById($request->getParam("confirm_uid"));
            $this->view->userInfoDataForPasswordChange = $userInfo;
        }
        
        if ($request->isPost()) {
            $credentialInfo = $credential->getByUid($uid);
            
            if (! $credentialInfo) {
                $this->_redirect($router->assemble(array(), "index"), array("exit"));
            }
            
            $registry->set('credentialInfoDataForPasswordChange', $credentialInfo);
            
            if ($form->isValid($request->getPost())) {
                $password = $form->getValue("password");
                
                if (isset($recoverInfo)) {
                    $recover->closeCase($uid);
                }
                
                $credential->setCredential($uid, $password);
                
                $this->view->passwordReset = true;
            }
        }
        if (! isset($this->view->passwordReset)) {
            $this->view->passwordForm = $form;
        }
    }
    
}