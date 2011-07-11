<?php
class PasswordController extends Zend_Controller_Action
{
	protected function _getNewPasswordForm($uid = false, $security_code = false)
    {
        $registry = Zend_Registry::getInstance();
        
        $config = $registry->get("config");
        
        static $form = '';
        
        if(!is_object($form))
        {
        	require_once APPLICATION_PATH . '/forms/NewPassword.php';
        	
        	if(!$uid)
        	{
        		$path = Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), "password");
        	} else {
        		$path = Zend_Controller_Front::getInstance()->getRouter()->assemble(array("confirm_uid" => $uid, "security_code" => $security_code), "password_unsigned");
        	}
        	
            $form = new Form_NewPassword(array(
                'action' => ($config->ssl) ? 'https://'.$config->webhostssl . $config->webroot . $path : $config->webroot . $path,
                'method' => 'post',
            ));
            
        }
        return $form;
    }
	
    public function unavailableAction()
    {
    }
    
    public function recoverAction()
    {
        if(Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect(Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), "logout") . "?please", array("exit"));
		}
    	
    	$request = $this->getRequest();
    	
    	$registry = Zend_Registry::getInstance();
        $config = $registry->get('config');
        
        $People = ML_People::getInstance();
        $Recover = ML_Recover::getInstance();
        
        $form = $Recover->_getRecoverForm();
        
        if($request->isPost() && $form->isValid($request->getPost()))
        {
        	$find = $form->getValues();
        	
        	$securitycode = sha1(md5(mt_rand(0, 1000).time().$find.microtime()).deg2rad(mt_rand(0,360)));
        	$getUser = $registry->accountRecover; //AccountRecover.php validator who tells
        	
        	$Recover->getAdapter()->query('INSERT INTO `recover` (`uid`, `securitycode`) VALUES (?, ?) ON DUPLICATE KEY UPDATE uid=VALUES(uid), securitycode=VALUES(securitycode), timestamp=CURRENT_TIMESTAMP', array($getUser['id'], $securitycode));
        	
        	$this->view->securitycode = $securitycode;
        	$this->view->recoverUser = $getUser;
        	
        	$this->view->recovering = true;
        	
	        $mail = new Zend_Mail();
			$mail->setBodyText($this->view->render("password/emailRecover.phtml"))
				->setFrom($config->robotEmail->addr, $config->robotEmail->name)
				->addTo($getUser['email'], $getUser['name'])
				->setSubject('Recover your '.$config->applicationname.' account')
				->send();
        }
    	
    	$this->view->recoverForm = $form;
    }
    
    public function passwordAction()
    {
    	$request = $this->getRequest();
    	
    	$auth = Zend_Auth::getInstance();
    	$registry = Zend_Registry::getInstance();
    	
    	$People = ML_People::getInstance();
    	$Credential = ML_Credential::getInstance();
    	$Recover = ML_Recover::getInstance();
    	
    	$params = $request->getParams();
    	
    	$this->view->request = $request;
    	
    	if($auth->hasIdentity())
    	{
    	    if(!isset($params['confirm_uid']) && !isset($params['security_code']))
    	    {
        	    $form = $this->_getNewPasswordForm();
        		$uid = $auth->getIdentity();
    	    	$registry->set("changeUserProperPassword", true);
    	    } else {
    	        $registry->set("pleaseSignout", true);
    	        return $this->_forward("index", "logout");
    	    }
    	} else {
    	    if(isset($params['confirm_uid']) && isset($params['security_code']))
    	    {
    	        $select = $Recover->select()->where("uid = ?", $request->getParam("confirm_uid"))
    			->where("securitycode = ?", $request->getParam("security_code"))
    			->where("CURRENT_TIMESTAMP < TIMESTAMP(timestamp, '12:00:00')");
    		    $recoverInfo = $Recover->fetchRow($select);
    		    
    		    if(!is_object($recoverInfo))
    		    {
    		        //error: password change not open
    		        $this->getResponse()->setHttpResponseCode(404);
    		        return $this->_forward("unavailable");
    		    }
    		    
        		$recoverInfoData = $recoverInfo->toArray();
	    		
    	    	$form = $this->_getNewPasswordForm($request->getParam("confirm_uid"), $request->getParam("security_code"));
	    	    
    		    $uid = $recoverInfoData['uid'];
    	    } else {
    	        return $this->_forward("redirect", "login");
    	    }
    	}
        
        
	    if($request->isPost())
		{
			
			$select = $Credential->select()->where("uid = ?", $uid);
			$credentialInfo = $Credential->fetchRow($select);
			if(!is_object($credentialInfo)) {
				$this->_redirect(Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), "index"), array("exit"));
			}
			$credentialInfoData = $credentialInfo->toArray();
			
			Zend_Registry::getInstance()->set('credentialInfoDataForPasswordChange', $credentialInfoData);
			
			if($form->isValid($request->getPost())) {
				$password = $form->getValue("password");
				$hash = ML_Credential::getHash($credentialInfo['uid'], $credentialInfo['membershipdate'] ,$password);
				
				if(isset($RecoverInfo)) {
					$Recover->delete($Recover->getAdapter()->quoteInto('uid = ?', $uid));
				}
				$Credential->getAdapter()->query('INSERT INTO `credentials` (`uid`, `credential`) VALUES (?, ?) ON DUPLICATE KEY UPDATE credential=VALUES(credential)', array($uid, $hash));
				
				$this->view->passwordReset = true;
			}
		}
		
		if(!isset($this->view->passwordReset)) {
			$this->view->passwordForm = $form;
		}
    }
    
}