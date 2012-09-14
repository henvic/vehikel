<?php

/**
 * Login Controller
 *
 * @version    $Id:$
 * @since      File available since Release 0.1
 */
class LoginController extends Ml_Controller_Action
{
    /**
     * Redirects the user after sign in to
     * the page open before, to unprotected HTTP
     */
    public function gobackAction()
    {
        $credential =  $this->_sc->get("credential");
        /** @var $credential \Ml_Model_Credential() */

        $redirLink = $credential->checkLinkToRedirect();

        if (! $redirLink) {
            $redirLink = $this->_router->assemble(array(), "index");
        }

        //never to use $this->_config['webroot'] . here because $redirLink already contains it
        $this->_redirect("http://" . $this->_config['webhost'] . $redirLink, array("exit"));
    }

    /**
     * Redirects the user to the login page
     */
    public function redirectAction()
    {
        $params = $this->_request->getParams();

        if ($this->_config['ssl']) {
            $loginFallback = "https://" . $this->_config['webhostssl'];// no place for $this->_config['webroot'] here
        } else {
            $loginFallback = "http://" . $this->_config['webhost'];
        }

        $loginFallback  .=
            $this->_router->assemble(array(), "login") . "?redirect_after_login=" .
                $this->_router->assemble($params, $this->_router->getCurrentRouteName());

        $this->_redirect($loginFallback, array("exit"));
    }

    public function indexAction()
    {
        $sessionConfig = $this->_config['resources']['session'];

        Ml_Model_AntiAttack::loadRules();

        $people =  $this->_sc->get("people");
        /** @var $people \Ml_Model_People() */

        $credential =  $this->_sc->get("credential");
        /** @var $credential \Ml_Model_Credential() */

        $logger =  $this->_sc->get("logger");
        /** @var $logger \Ml_Logger() */

        if ($this->_auth->hasIdentity()) {
            return $this->_forward("goback");
        }

        $form = new Ml_Form_Login(null, $this->_auth, $this->_config, $people, $credential);

        if (Ml_Model_AntiAttack::ensureHuman()) {
            $ensureHuman = true;
        } else {
            $ensureHuman = false;
        }

        $this->view->loginform = $form;

        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {

            $userInfo = $people->get($form->getValue("username"));

            if (! $userInfo) {
                throw new Exception("Couldn't retrieve userInfo for login");
            }

            $adapter = $credential->getAuthAdapter($userInfo["id"], $form->getValue("password"));
            $result = $this->_auth->authenticate($adapter);

            if (! $result->isValid()) {
                throw new Exception("Login failure");
            }

            $session =  $this->_sc->get("session");
            /** @var $session \Ml_Model_Session() */

            //rememberMe and ForgetMe already regenerates the ID
            if ($form->getElement("remember_me")->isChecked()) {
                Zend_Session::rememberMe($sessionConfig['cookie_lifetime']);
            } else {
                Zend_Session::ForgetMe();
            }

            $session->associate($this->_auth->getIdentity(), Zend_Session::getId());

            $logger->log(array("action" => "login",
                "username" => $form->getValue("username")));

            return $this->_forward("goback");
        }

        $challenge = $form->getElement("challenge");

        //don't show missing value in the first time that asks for the captcha
        if (! $ensureHuman && is_object($challenge)) {
            $challenge->setErrorMessages(array("missingValue" => ''));
        }
    }
}
