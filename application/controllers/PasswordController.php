<?php
class PasswordController extends Ml_Controller_Action
{
    public function unavailableAction()
    {
        $this->getResponse()->setHttpResponseCode(410);
    }

    public function recoverAction()
    {
        if ($this->_auth->hasIdentity()) {
            $this->_redirect($this->_router->assemble(array(), "logout") . "?please", array("exit"));
        }

        $recover =  $this->_sc->get("recover");
        /** @var $recover \Ml_Model_Recover() */

        $people =  $this->_sc->get("people");
        /** @var $people \Ml_Model_People() */

        $form = new Ml_Form_Recover(null, $people, $this->_config);

        if (! $this->_request->isPost() || ! $form->isValid($this->_request->getPost())) {
            $this->view->recoverForm = $form;
        } else {
            $data = $form->getValues();

            $userInfo = $people->get($data['recover']);

            if (! $userInfo) {
                throw new Exception("Error in retrieving userInfo");
            }

            $recoverInfo = $recover->create($userInfo['id']);

            if (! $recoverInfo) {
                throw new Exception("Error in creating security code");
            }

            $this->view->userInfo = $userInfo;

            $this->view->recoverInfo = $recoverInfo;

            $mail = new Zend_Mail('UTF-8');

            $mail
                ->setBodyText($this->view->render("password/email-recover.phtml"))
                ->addTo($userInfo['email'], $userInfo['name'])
                ->setSubject('Perdeu seu usuário ou senha?')
                ->send();

            $this->render("check-email-recover");
        }
    }


    public function recoveringAction()
    {
        $people =  $this->_sc->get("people");
        /** @var $people \Ml_Model_People() */

        $credential =  $this->_sc->get("credential");
        /** @var $credential \Ml_Model_Credential() */

        $recover =  $this->_sc->get("recover");
        /** @var $recover \Ml_Model_Recover() */

        $params = $this->_request->getParams();

        $recoverInfo = $recover->read($params["confirm_uid"], $params["security_code"]);

        if (! $recoverInfo) {
            return $this->_forward("unavailable");
        }

        $userInfo = $people->getById($recoverInfo['uid']);

        if (! $userInfo) {
            throw new Exception("Error in retrieving userInfo.");
        }

        $form = new Ml_Form_RedefinePassword(
            null,
            $this->_config,
            $credential,
            $params["confirm_uid"],
            $params["security_code"]
        );

        if (! $this->_request->isPost() || ! $form->isValid($this->_request->getPost())) {
            $form->setDefault("username", $userInfo['username']);

            $this->view->redefinePasswordForm = $form;
        } else {
            $recover->delete($recoverInfo["uid"], $recoverInfo["security_code"]);
            $credential->set($recoverInfo["uid"], $form->getValue("password"));

            $this->render("redefined");
        }
    }

    public function passwordAction()
    {
        $people =  $this->_sc->get("people");
        /** @var $people \Ml_Model_People() */

        $credential =  $this->_sc->get("credential");
        /** @var $credential \Ml_Model_Credential() */

        $params = $this->_request->getParams();

        if (! $this->_auth->hasIdentity()) {
            return $this->_forward("redirect", "login");
        }

        $userInfo = $this->_registry->get("signedUserInfo");

        $form = new Ml_Form_NewPassword(null, $this->_config, $credential, $userInfo["id"]);

        if (! $this->_request->isPost() || ! $form->isValid($this->_request->getPost())) {
            $form->setDefault("username", $userInfo['username']);

            $this->view->redefinePasswordForm = $form;
        } else {
            $credential->set($userInfo["id"], $form->getValue("password"));

            $this->render("redefined");
        }

    }

}