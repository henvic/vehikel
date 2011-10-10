<?php

/**
 * Login Controller
 *
 * @version    $Id:$
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
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $config = $registry->get("config");
        
        $credential = Ml_Model_Credential::getInstance();
        
        $redirLink = $credential->checkLinkToRedirect();
        
        if (! $redirLink) {
            $redirLink = $router->assemble(array(), "index");
        }
        
        //never to use $config['webroot'] . here because $redirLink already contains it
        $this->_redirect("http://" . $config['webhost'] . $redirLink, array("exit"));
    }
    
    /**
     * Redirects the user to the login page
     */
    public function redirectAction()
    {
        $registry = Zend_Registry::getInstance();
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $config = $registry->get("config");
        
        $request = $this->getRequest();
        
        $params = $request->getParams();
        
        if ($config['ssl']) {
            $loginFallback = "https://" . $config['webhostssl'];// no place for $config['webroot'] here
        } else {
            $loginFallback = "http://" . $config['webhost'];
        }
        
        $loginFallback  .=
        $router->assemble(array(), "login") . "?redirect_after_login=" .
        $router->assemble($params, $router->getCurrentRouteName());
        
        $this->_redirect($loginFallback, array("exit"));
    }
    
    public function indexAction()
    {
        $registry = Zend_Registry::getInstance();
        $auth = Zend_Auth::getInstance();
        
        $config = $registry->get("config");
        $sessionConfig = $config['resources']['session'];
        
        Ml_Model_AntiAttack::loadRules();
        $credential = Ml_Model_Credential::getInstance();
        $logger = Ml_Model_Logger::getInstance();
        
        if ($auth->hasIdentity()) {
            return $this->_forward("goback");
        }
        
        $request = $this->getRequest();
        $form = $credential->loginForm();
        
        if (Ml_Model_AntiAttack::ensureHuman()) {
            $ensureHuman = true;
        } else {
            $ensureHuman = false;
        }
        
        
        if ($request->isPost()) {
        
        ignore_user_abort(true);
        
        //A way to sign in only if captcha is right. This is a workaround to
        //signout if the captcha is wrong.
        //
        //I've decided to put the sign in code in the validator itself,
        //but couldn't find a way to make the password validator
        //load after the captcha one (but to let it come first in code,
        //and that's ugly on the screen) and get a result if the
        //validation worked. Notice that it is only useful when
        //the captcha is required.
        if ($form->isValid($request->getPost())) {//@see below
            $session = Ml_Model_Session::getInstance();
            
            //rememberMe and ForgetMe already regenerates the ID
            if ($form->getElement("remember_me")->isChecked()) {
                Zend_Session::rememberMe($sessionConfig['cookie_lifetime']);
            } else {
                Zend_Session::ForgetMe();
            }
            
            $session->associate($auth->getIdentity(), Zend_Session::getId());
            
            $logger->log(array("action" => "login",
            "username" => $form->getValue("username")));
            
            $this->_forward("goback");
        } else {
            //@see above
            if ($auth->hasIdentity()) {
                $auth->clearIdentity();
            }
            
            $logger->log(array("action" => "login_denied",
            "username" => $form->getValue("username")));
            
            $this->view->errorlogin = true;
        }//@end of workaround
        }
        $challenge = $form->getElement("challenge");
        
        //don't show missing value in the first time that asks for the captcha
        if (! $ensureHuman && is_object($challenge)) {
            $challenge->setErrorMessages(array("missingValue" => ''));
        }
        
        $this->view->loginform = $form;
    }
}
