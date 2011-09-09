<?php
class PasswordController extends Zend_Controller_Action
{
    protected function _getNewPasswordForm($uid = false, $securityCode = false)
    {
        $registry = Zend_Registry::getInstance();
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $config = $registry->get("config");
        
        static $form = '';
        
        if (! is_object($form)) {
            require APPLICATION_PATH . '/forms/NewPassword.php';
            
            if (! $uid) {
                $path = $router->assemble(array(), "password");
            } else {
                $path = $router->assemble(array("confirm_uid" => $uid,
                "security_code" => $securityCode),
                "password_unsigned");
            }
            
            if ($config['ssl']) {
                $action = 'https://' . $config['webhostssl'] . $config['webroot'] . $path;
            } else {
                $action = $config['webroot'] . $path;
            }
            
            $form = new Form_NewPassword(array('action' => $action,
                'method' => 'post',
            ));
            
        }
        return $form;
    }
    
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
        
        $people = ML_People::getInstance();
        $recover = ML_Recover::getInstance();
        
        $form = $recover->_getRecoverForm();
        
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $find = $form->getValues();
            
            $securitycode = sha1(md5(mt_rand(0, 1000) . time() . $find . microtime()) .
             deg2rad(mt_rand(0, 360)));
            
            //AccountRecover.php validator pass this data
            $getUser = $registry->accountRecover;
            
            $recover->getAdapter()
            ->query('INSERT INTO `recover` (`uid`, `securitycode`) VALUES (?, ?) ON DUPLICATE KEY UPDATE uid=VALUES(uid), securitycode=VALUES(securitycode), timestamp=CURRENT_TIMESTAMP',
            array($getUser['id'], $securitycode));
            
            $this->view->securitycode = $securitycode;
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
        
        $people = ML_People::getInstance();
        $credential = ML_Credential::getInstance();
        $recover = ML_Recover::getInstance();
        
        $params = $request->getParams();
        
        $this->view->request = $request;
        
        if ($auth->hasIdentity()) {
            if (isset($params['confirm_uid'])) {
                $this->_redirect($router->assemble(array(), "logout") . "?please", array("exit"));
            }
            
            $form = $this->_getNewPasswordForm();
            $uid = $auth->getIdentity();
            $registry->set("changeUserProperPassword", true);
            
            $signedUserInfo = $registry->get("signedUserInfo");
            
        } else {
            if (isset($params['confirm_uid']) && isset($params['security_code'])) {
                $select = $recover->select()->where("uid = ?", $request->getParam("confirm_uid"))
                ->where("securitycode = ?", $request->getParam("security_code"))
                ->where("CURRENT_TIMESTAMP < TIMESTAMP(timestamp, '12:00:00')");
                $recoverInfo = $recover->fetchRow($select);
                
                if (! is_object($recoverInfo)) {
                    return $this->_forward("unavailable");
                }
                
                $recoverInfoData = $recoverInfo->toArray();
                
                $form = $this->_getNewPasswordForm($request->getParam("confirm_uid"), 
                $request->getParam("security_code"));
                
                $uid = $recoverInfoData['uid'];
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
            $select = $credential->select()->where("uid = ?", $uid);
            $credentialInfo = $credential->fetchRow($select);
            
            if (! is_object($credentialInfo)) {
                $this->_redirect($router->assemble(array(), "index"), array("exit"));
            }
            
            $credentialInfoData = $credentialInfo->toArray();
            
            $registry->set('credentialInfoDataForPasswordChange', $credentialInfoData);
            
            if ($form->isValid($request->getPost())) {
                $password = $form->getValue("password");
                
                if (isset($recoverInfo)) {
                    $recover->delete($recover->getAdapter()->quoteInto('uid = ?', $uid));
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