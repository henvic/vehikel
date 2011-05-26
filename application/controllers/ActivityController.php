<?php
// Not being used right now
class ActivityController extends Zend_Controller_Action
{
	public function recentAction()
	{
	    if(!Zend_Auth::getInstance()->hasIdentity()) {
    	    Zend_Controller_Front::getInstance()->registerPlugin(new ML_Plugins_LoginRedirect());
    	}
		
		$this->view->headTitle("Recent activity");
	}
}
