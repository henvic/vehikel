<?php

/**
 * Login Controller
 *
 * Based on http://weierophinney.net/matthew/archives/165-Login-and-Authentication-with-Zend-Framework.html
 *
 * @version    $Id:$
 * @link       http://thinkings.info
 * @since      File available since Release 0.1
*/
class LoginController extends Zend_Controller_Action
{
    /**
     * Redirects the user after sign in to
     * the page open before, to unprotected HTTP
     */
    public function gobackAction()
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        $Credential = ML_Credential::getInstance();
        
        $redirLink = $Credential->checkLinkToRedirect();
    	if(!$redirLink)
    	{
    	    $redirLink = Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), "index");
    	}
    	
    	$this->_redirect("http://" . $config->webhost . $redirLink, array("exit"));//don't use $config->webroot . here
    }
    
    /**
     * Redirects the user to the login page
     */
    public function redirectAction()
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $request = $this->getRequest();
        
        $params = $request->getParams();
        
    	if($config->ssl)
        {
            $login_fallback = "https://" . $config->webhostssl;// no place for $config->webroot here
        } else {
            $login_fallback = "http://" . $config->webhost;
        }
    	$login_fallback  .= $router->assemble(array(), "login") . "?redirect_after_login=" . $router->assemble($params, $router->getCurrentRouteName());
    	
    	$this->_redirect($login_fallback, array("exit"));
    }
    
    public function indexAction()
    {
    	ML_AntiAttack::loadRules();
    	$Credential = ML_Credential::getInstance();
    	
    	if(Zend_Auth::getInstance()->hasIdentity())
    	{
    	    return $this->_forward("goback");
    	}
    	
        $request = $this->getRequest();
        $form = $Credential->_getLoginForm();
        
        $ensureHuman = (ML_AntiAttack::ensureHuman()) ? true : false;
        
        if($request->isPost()) {
        	
        ignore_user_abort(true);
        	
        //A way to sign in only if captcha is right. This is a workaround to signout if the captcha is wrong
	    //I've decided to put the sign in code in the validator itself, but couldn't find a way to make the password validator
	    //load after the captcha one (but to let it come first in code, and that's ugly on the screen) and get a result if the
	    //validation worked. Notice that it is only useful when the captcha is required.
        if($form->isValid($request->getPost())) {//@see below
        	if($form->getElement("remember_me")->isChecked()) Zend_Session::rememberMe(60 * 60 * 24 * 15);
        	Zend_Session::regenerateId();
        	$Log = ML_Log::getInstance();
        	$Log->action("login", null, $form->getValue("username"));
        	$this->_forward("goback");
	    } else {
	    	//@see above
	    	if(Zend_Auth::getInstance()->hasIdentity())
	    	{
		    	Zend_Auth::getInstance()->clearIdentity();
	   		}
	   		$Log = ML_Log::getInstance();
        	$Log->action("failed_login", null, $form->getValue("username"));
		    $this->view->errorlogin = true;
	    }//@end of workaround
        }
	    $challenge = $form->getElement("challenge");
	    if(!$ensureHuman && is_object($challenge)) $challenge->setErrorMessages(array("missingValue" => ''));//don't show missing value in the first time that asks for the captcha
		$this->view->loginform = $form;
    }
}
