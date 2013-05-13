<?php

class AccountController extends Ml_Controller_Action
{
    public function preDispatch()
    {
        if (! $this->_auth->hasIdentity()) {
            return $this->_forward("redirect", "login");
        }
    }

    public function indexAction()
    {
        $people =  $this->_sc->get("people");
        /** @var $people \Ml_Model_People() */

        $signedUserInfo = $this->_registry->get("signedUserInfo");

        $form = new Ml_Form_AccountSettings(null, $people, array($signedUserInfo["email"]));

        //only data that can be changed can be here
        $editableData = array(
            "name" => $signedUserInfo['name'],
            "account_type" => $signedUserInfo['account_type'],
            "email" => $signedUserInfo['email'],
            "private_email" => $signedUserInfo['private_email'],
        );

        $form->setDefaults(array_merge(
            $editableData,
            array("username" => $signedUserInfo["username"])
        ));

        $this->view->accountForm = $form;

        if (! $this->_request->isPost() || ! $form->isValid($this->_request->getPost())) {
            return;
        }

        $changes = array_diff($form->getValues(), $editableData);

        unset($changes["hash"]);

        if (isset($changes['private_email'])) {
            if ($changes['private_email']) {
                $changes['private_email'] = true;
            } else {
                $changes['private_email'] = false;
            }
        }

        $userInfoDataFields = array("name", "account_type", "private_email");

        $userInfoDataChanges = array();
        foreach ($userInfoDataFields as $field) {
            if (isset($changes[$field])) {
                $userInfoDataChanges[$field] = $changes[$field];
            }
        }
        unset($field);

        if (! empty($userInfoDataChanges)) {
            $people->update($signedUserInfo['id'], $userInfoDataChanges);
            $signedUserInfo = array_merge($signedUserInfo, $userInfoDataChanges);
            $this->_registry->set("signedUserInfo", $signedUserInfo);
        }

        if (isset($changes["email"])) {
            // the value on the email field shouldn't change at this time
            $form->getElement("email")->setValue($signedUserInfo["email"]);

            $emailChange =  $this->_sc->get("emailChange");
            /** @var $emailChange \Ml_Model_EmailChange() */

            $changeInfo = $emailChange->create($signedUserInfo['id'], $changes['email']);

            if (! is_array($changeInfo)) {
                throw new Exception("Can't create email change request");
            }

            $mail = new Zend_Mail('UTF-8');

            $this->view->securityCode = $changeInfo["security_code"];
            $this->view->signedUserInfo = $signedUserInfo;

            $mail->setBodyText($this->view->render("account/email-change.phtml"))
                ->addTo($changes['email'], $signedUserInfo['name'])
                ->setSubject('Atualização de endereço de email')
                ->send();

            $this->view->changeEmail = true;
        }
    }

    public function templateAction()
    {
        $signedUserInfo = $this->_registry->get("signedUserInfo");

        $people =  $this->_sc->get("people");
        /** @var $people \Ml_Model_People() */

        $form = new Ml_Form_PostTemplate();

        $this->view->addJsParam("route", "account/template");

        $this->view->assign("postTemplateForm", $form);

        $form->setDefault("post_template", $signedUserInfo["post_template"]);

        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $data = ["post_template" => $form->getValue("post_template")];
            $people->update($signedUserInfo["id"], $data);
        }
    }

    public function pictureAction()
    {
        $signedUserInfo = $this->_registry->get("signedUserInfo");

        $picture =  $this->_sc->get("picture");
        /** @var $picture \Ml_Model_Picture() */

        $people =  $this->_sc->get("people");
        /** @var $people \Ml_Model_People() */

        $this->view->addJsParam("route", "account/picture");

        $form = new Ml_Form_Picture();

        $this->view->submitPictureForm = $form;

        if ($signedUserInfo["picture_id"]) {
            $this->view->pictureLink = $picture->getImageLink($signedUserInfo["picture_id"]);
        }

        if (! $this->_request->isPost() || ! $form->isValid($this->_request->getPost())) {
            return true;
        }

        if ($form->getValue("delete")) {
            $people->update($signedUserInfo["id"], array("picture_id" => false));
            $picture->delete($signedUserInfo["picture_id"], $signedUserInfo["id"]);
            $this->_redirect($this->_router->assemble(array(), "account_picture"), array("exit"));
        } else if ($form->Image->isUploaded()) {
            $fileInfo = $form->Image->getFileInfo();
            $pictureId = $picture->create($fileInfo['Image']['tmp_name'], $signedUserInfo['id']);

            if ($pictureId) {
                $people->update($signedUserInfo['id'], array("picture_id" => $pictureId));

                //remove the old picture, if it exists
                $picture->delete($signedUserInfo["picture_id"]);
                $this->_redirect($this->_router->assemble(array(), "account_picture"), array("exit"));
            } else {
                throw new Exception("Error creating pictures.");
            }
        }
    }

    public function deleteAction()
    {
        $credential =  $this->_sc->get("credential");
        /** @var $credential \Ml_Model_Credential() */

        $logger = $this->_sc->get("logger");
        /** @var $logger \Ml_Logger() */

        $session = $this->_sc->get("session");
        /** @var $session \Ml_Model_Session() */

        $account = $this->_sc->get("account");
        /** @var $account \Ml_Model_Account */

        $signedUserInfo = $this->_registry->get("signedUserInfo");

        $form = new Ml_Form_DeleteAccount(null, $credential, $signedUserInfo["id"], $this->_config);

        $this->view->deleteAccountForm = $form;

        if (! $this->_request->isPost() || ! $form->isValid($this->_request->getPost())) {
            return;
        }

        $result = $account->deactive($signedUserInfo["id"]);

        if (! $result) {
            throw new Exception("Failure in deactivating account");
        }

        $logger->log(array(
            "action" => "deactive_account",
            "uid" => $signedUserInfo["id"]
        ));

        $session->remoteLogout();
        $session->logout();

        $this->_redirect("/help/account/termination", array("exit"));
    }

    public function addressAction()
    {
        $signedUserInfo = $this->_registry->get("signedUserInfo");

        $people =  $this->_sc->get("people");
        /** @var $people \Ml_Model_People() */

        $form = new Ml_Form_Address();

        $this->view->signedUserInfo = $signedUserInfo;

        $this->view->addressForm = $form;

        $currentAddress = $signedUserInfo["address"];

        if ($currentAddress) {
            $currentAddress["phone"] = mb_substr($currentAddress["phone"], 3);

            $form->setDefaults(
                $currentAddress
            );
        }

        if (! $this->getRequest()->isPost() || ! $form->isValid($this->getRequest()->getPost())) {
            return true;
        }

        $values = $form->getValues();

        $address = [
            "street_address" => $values["street_address"],
            "neighborhood" => $values["neighborhood"],
            "locality" => $values["locality"],
            "region" => $values["region"],
            "postal_code" => $values["postal_code"],
            "country_name" => "Brasil"
        ];

        $address["phone"] = "+55" . str_replace(array(" ", "-"), "", $values["phone"]);

        $people->update($signedUserInfo['id'], array("address" => json_encode($address)));

        $this->_redirect($this->_router->assemble(array(), "account_address"), array("exit"));
    }
}
