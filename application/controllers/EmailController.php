<?php

class EmailController extends Ml_Controller_Action
{
    public function unavailableAction()
    {
        $this->getResponse()->setHttpResponseCode(410);
    }

    public function confirmAction()
    {
        $people =  $this->_sc->get("people");
        /** @var $people \Ml_Model_People() */

        $emailChange =  $this->_sc->get("emailChange");
        /** @var $emailChange \Ml_Model_EmailChange() */
        
        $confirmUid = $this->_request->getParam("confirm_uid");
        $securityCode = $this->_request->getParam("security_code");
        
        $changeInfo = $emailChange->read($confirmUid, $securityCode);
        
        if (! $changeInfo) {
            return $this->_forward("unavailable");
        }

        $emailChange->delete($confirmUid, $securityCode);
        $people->update($confirmUid, array("email" => $changeInfo["new_email"]));
    }
}