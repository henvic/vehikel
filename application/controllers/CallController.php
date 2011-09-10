<?php

class CallController extends Zend_Controller_Action
{
    public function init()
    {
        $registry = Zend_Registry::getInstance();
        
        $auth = Zend_Auth::getInstance();
        
        if (! $auth->hasIdentity()) {
            Zend_Controller_Front::getInstance()->registerPlugin(new Ml_Plugins_LoginRedirect());
        }
    }
    
    public function indexAction()
    {
        $registry = Zend_Registry::getInstance();
        $signedUserInfo = $registry->get("signedUserInfo");
        
        $calls = Ml_Calls::getInstance();
        $form = $calls->form();
        
        $credits = Ml_Credits::getInstance();
        
        $credits->transaction($signedUserInfo['id'], -111, Ml_Credits::cents_USD, "transfer", "0");
        
        $this->view->callForm = $form;
    }
}