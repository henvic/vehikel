<?php
// report_abuse
class AbuseController extends Zend_Controller_Action
{
    public function reportAction ()
    {
        $auth = Zend_Auth::getInstance();
        $request = $this->getRequest();
        
        $abuse = new Ml_Model_Abuse();
        $form = $abuse->form();
        
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $abuse->insert(
            array("referer" => $form->getValue("abuse_reference"), 
            "description" => $form->getValue("abuse_description"), 
            "byUid" => $auth->getIdentity(), "byAddr" => $_SERVER['REMOTE_ADDR']));
            $this->view->reportDone = true;
        }
        $this->view->abuseForm = $form;
    }
}
