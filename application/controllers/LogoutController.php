<?php 
class LogoutController extends Zend_Controller_Action
{
    public function indexAction()
    {
    	if(!Zend_Auth::getInstance()->hasIdentity()) {
    		$this->_redirect(Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), "index"), array("exit"));
    	}
    	
		$request = $this->getRequest();
    	$params = $request->getParams();
    	
    	$Credential = ML_Credential::getInstance();
    	$Session = ML_Session::getInstance();
    	
    	$form = $Credential->_getLogoutForm();
    	
    	
    	if($request->isPost() && $form->isValid($request->getPost())) {
        	
	        ignore_user_abort(true);
	        
	        $unfiltered_values = $form->getUnfilteredValues();
	        
	        if(isset($unfiltered_values['remote_signout']))
	        {
	        	
	    		$Session->remoteLogout();
	    		$this->view->remote_logout_done = true;
	        } else {
	        	$Session->logout();
		    	$this->_redirect(Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), "index"), array("exit"));
	        }
    	}
    	
    	$this->view->logoutForm = $form;
    }
}