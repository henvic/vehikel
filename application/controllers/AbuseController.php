<?php
// report_abuse
class AbuseController extends Zend_Controller_Action
{
    public function reportAction ()
    {
        $auth = Zend_Auth::getInstance();
        $request = $this->getRequest();
        
        $Abuse = new ML_Abuse();
        $form = $Abuse->form();
        
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $Abuse->insert(
            array("referer" => $form->getValue("abuse_reference"), 
            "description" => $form->getValue("abuse_description"), 
            "byUid" => $auth->getIdentity(), "byAddr" => $_SERVER['REMOTE_ADDR']));
            $this->view->report_done = true;
        }
        $this->view->abuseForm = $form;
    }
}
