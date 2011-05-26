<?php

class EmailController extends Zend_Controller_Action
{
	public function confirmAction()
    {
    	$request = $this->getRequest();
    	
    	$auth = Zend_Auth::getInstance();
    	
    	$emailChange = new ML_emailChange();
    	
    	$confirm_uid = $request->getParam("confirm_uid");
    	$security_code = $request->getParam("security_code");
    	
    	$select = $emailChange->select()->where("uid = ?", $confirm_uid)
    	->where("securitycode = ?", $security_code)
    	->where("CURRENT_TIMESTAMP < TIMESTAMP(timestamp, '12:00:00')");
    	$changeInfo = $emailChange->fetchRow($select);
    	if(!is_object($changeInfo)) {
    	    $this->_redirect("/email/unconfirmed", array("exit"));
    	}
    	
    	$changeInfoData = $changeInfo->toArray();
    	$People = ML_People::getInstance();
    	try {
    		$People->update(array("email" => $changeInfoData['email']), $People->getAdapter()->quoteInto("id = ?", $changeInfoData['uid']));
    		$emailChange->delete($emailChange->getAdapter()->quoteInto('uid = ?', $changeInfoData['uid']));
    	} catch(Exception $e)
    	{
    		$this->_redirect(Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), "index"), array("exit"));
    	}
    	
    	if($auth->hasIdentity()) {
    		if($auth->getIdentity() == $changeInfoData['uid']) {
    			
    			//clear cache
    			$defaultNamespace = new Zend_Session_Namespace();
    			unset($defaultNamespace->cachedSignedUserInfo);
    			
    			$signedUserInfo = $People->getById($changeInfoData['uid']);//refresh
    			//$registry->set("signedUserInfo", $signedUserInfo);//not necessary because of the redirect
    			
    			$defaultNamespace->cachedSignedUserInfo = $signedUserInfo;
    			
    			$this->_redirect($this->view->StaticUrl("/email/confirmed"), array("exit"));
    		} else {
    		    //@todo methods to force session's cache (for the remote user) to be refreshed
    			$this->_redirect($this->view->StaticUrl("/email/confirmed"), array("exit"));
    		}
    	}
	    $this->_redirect($this->view->StaticUrl("/email/confirmed"), array("exit"));
    }
}