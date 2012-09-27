<?php
/**
 * Sign Up
*/

class JoinController extends Ml_Controller_Action
{
    public function indexAction()
    {
        if ($this->_auth->hasIdentity()) {
            $this->_redirect($this->_router->assemble(array(), "logout") . "?please", array("exit"));
        }

        $signUp =  $this->_sc->get("signup");
        /** @var $signUp \Ml_Model_SignUp() */

        $people =  $this->_sc->get("people");
        /** @var $people \Ml_Model_People() */

        $form = new Ml_Form_SignUp(null, $this->_config, $people);
        
        if (! $this->_request->isPost() || ! $form->isValid($this->_request->getPost())) {
            $this->view->signUpForm = $form;
        } else {
            $data = $form->getValues();
            
            $newUserInfo = $signUp->create($data['name'], $data['email']);

            if (! $newUserInfo) {
                throw new Exception("Could not sign up the user.");
            }

            $this->view->newUserInfo = $newUserInfo;

            $mail = new Zend_Mail('UTF-8');

            $mail->setBodyText($this->view->render("join/email.phtml"))
                ->addTo($data['email'], $data['name'])
                ->setSubject('Cadastro')
                ->send();

            $this->render("check-email");
        }
    }

    public function unavailableAction()
    {
        $this->getResponse()->setHttpResponseCode(410);
    }

    public function confirmAction()
    {
        if ($this->_auth->hasIdentity()) {
            $this->_redirect($this->_router->assemble(array(), "logout") . "?please", array("exit"));
        }

        if ($this->_config['ssl'] && (! isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on")) {
            $this->_redirect("https://" . $this->_config['webhostssl'] .
                $this->_router->assemble(array($this->_request->getUserParams()),
                    "join_emailconfirm"), array("exit"));
        }

        $signUp =  $this->_sc->get("signup");
        /** @var $signUp \Ml_Model_SignUp() */

        $securityCode = $this->_request->getParam("security_code");
        $read = $signUp->read($securityCode);

        if (! $read) {
            return $this->_forward("unavailable");
        }

        $people =  $this->_sc->get("people");
        /** @var $people \Ml_Model_People() */
        $userInfo = $people->getByEmail($read["email"]);

        if (is_array($userInfo)) {
            $read = false;
            $signUp->delete($securityCode);
            return $this->_forward("unavailable");
        }

        $form = new Ml_Form_NewIdentity(null, $securityCode, $this->_config, $people);

        $form->populate(
            array(
                "name" => $read["name"],
                "email" => $read["email"]
            )
        );

        if (! $this->_request->isPost() || ! $form->isValid($this->_request->getPost())) {
            $this->view->entry = $read;
            $this->view->confirmForm = $form;
            return true;
        }

        $values = $form->getValues();

        $people =  $this->_sc->get("people");
        /** @var $people \Ml_Model_People() */

        $credential =  $this->_sc->get("credential");
        /** @var $credential \Ml_Model_Credential() */

        ignore_user_abort(true);

        // remove authorization to create account
        $signUp->delete($securityCode);

        $userInfoId = $people->create($values["newusername"], $values["name"], $values["email"]);

        if (! $userInfoId) {
            throw new Exception("Could not create user account.");
        }

        $credential->set($userInfoId, $values["password"]);

        $adapter = $credential->getAuthAdapter($userInfoId, $values["password"]);

        $result = $this->_auth->authenticate($adapter);

        if ($result->getCode() != Zend_Auth_Result::SUCCESS) {
            throw new Exception("Could not authenticate: " . implode(" ", $result->getCode()));
        }

        Zend_Session::regenerateId();

        $session =  $this->_sc->get("session");
        /** @var $session \Ml_Model_Session() */
        $session->associate($this->_auth->getIdentity(), Zend_Session::getId());

        $this->_redirect($this->_router->assemble(array(), "join_welcome"), array("exit"));
    }
    
    public function welcomeAction()
    {
        if (! $this->_auth->hasIdentity()) {
            return $this->_forward("redirect", "login");
        }

        $this->view->joined = true;
    }
}
