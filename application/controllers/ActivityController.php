<?php
// Not being used right now
class ActivityController extends Zend_Controller_Action
{
    public function recentAction()
    {
        $auth = $auth;
        
        if (! $auth->hasIdentity()) {
            Zend_Controller_Front::getInstance()->registerPlugin(new Ml_Plugins_LoginRedirect());
        }
        
        $this->view->headTitle("Recent activity");
    }
}
