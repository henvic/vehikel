<?php

class CallController extends Zend_Controller_Action
{
	public function init()
    {
    	$registry = Zend_Registry::getInstance();
        if(!Zend_Auth::getInstance()->hasIdentity()) {
    	    Zend_Controller_Front::getInstance()->registerPlugin(new ML_Plugins_LoginRedirect());
    	}
    }
    
	public function indexAction()
	{
		$registry = Zend_Registry::getInstance();
		$signedUserInfo = $registry->get("signedUserInfo");
		
		$Calls = ML_Calls::getInstance();
		$form = $Calls->form();
		
		$Credits = ML_Credits::getInstance();
		
		$Credits->transaction($signedUserInfo['id'], -111, ML_Credits::cents_USD, "transfer", "0");
		
		$this->view->call_form = $form;
	}
}