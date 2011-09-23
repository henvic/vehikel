<?php

class EmailController extends Zend_Controller_Action
{
    public function confirmAction()
    {
        $auth = Zend_Auth::getInstance();
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $request = $this->getRequest();
        
        $people = Ml_Model_People::getInstance();
        
        $emailChange = Ml_Model_EmailChange::getInstance();
        
        $confirmUid = $request->getParam("confirm_uid");
        $securityCode = $request->getParam("security_code");
        
        $changeInfo = $emailChange->get($confirmUid, $securityCode);
        
        if (! $changeInfo) {
            $this->_redirect("/email/unconfirmed", array("exit"));
        }
        
        if ($auth->hasIdentity() && $changeInfo['uid'] != $auth->getIdentity()) {
            $this->_redirect($router->assemble(array(), "logout") . "?please", array("exit"));
        }
        
        $confirm = $emailChange->setChange($confirmUid, $changeInfo['email']);
        
        if ($confirm) {
            $this->_redirect($this->view->StaticUrl("/email/confirmed"), array("exit"));
        } else {
            throw new Exception("Couldn't confirm new e-mail.");
        }
    }
}