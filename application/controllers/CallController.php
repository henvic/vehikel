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
        
        $form = Ml_Model_Calls::form();
        
        $credits = Ml_Model_Credits::getInstance();
        
        $this->view->callForm = $form;
    }
}