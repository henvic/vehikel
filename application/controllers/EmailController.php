<?php

class EmailController extends Zend_Controller_Action
{
    public function confirmAction()
    {
        $auth = Zend_Auth::getInstance();
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $request = $this->getRequest();
        
        $emailChange = new Ml_Model_EmailChange();
        
        $confirmUid = $request->getParam("confirm_uid");
        $securityCode = $request->getParam("security_code");
        
        $select = $emailChange->select()->where("uid = ?", $confirmUid)
        ->where("securitycode = ?", $securityCode)
        ->where("CURRENT_TIMESTAMP < TIMESTAMP(timestamp, '12:00:00')");
        $changeInfo = $emailChange->fetchRow($select);
        
        if (! is_object($changeInfo)) {
            $this->_redirect("/email/unconfirmed", array("exit"));
        }
        
        $changeInfoData = $changeInfo->toArray();
        
        if ($auth->hasIdentity() && $changeInfo['uid'] != $auth->getIdentity()) {
            $this->_redirect($router->assemble(array(), "logout") . "?please", array("exit"));
        }
        
        $people = Ml_Model_People::getInstance();
        try {
            $people
            ->update(array("email" => $changeInfoData['email']),
            $people->getAdapter()
            ->quoteInto("id = ?", $changeInfoData['uid']));
            
            $emailChange->delete($emailChange->getAdapter()
            ->quoteInto('uid = ?', $changeInfoData['uid']));
        } catch(Exception $e)
        {
            $this->_redirect($router->assemble(array(), "index"), array("exit"));
        }
        
        $this->_redirect($this->view->StaticUrl("/email/confirmed"), array("exit"));
    }
}