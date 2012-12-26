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

        $profile =  $this->_sc->get("profile");
        /** @var $profile \Ml_Model_Profile() */

        $signedUserInfo = $this->_registry->get("signedUserInfo");

        $form = new Ml_Form_AccountSettings(null, $people, array($signedUserInfo["email"]));

        $profileInfo = $profile->getById($signedUserInfo['id']);

        //only data that can be changed can be here
        $editableData = array(
            "name" => $signedUserInfo['name'],
            "account_type" => $signedUserInfo['account_type'],
            "email" => $signedUserInfo['email'],
            "private_email" => $signedUserInfo['private_email'],
            "about" => $profileInfo['about'],
            "website" => $profileInfo['website'],
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

        $profileDataFields = array("website", "about");

        $profileDataChanges = array();
        foreach ($profileDataFields as $field) {
            if (isset($changes[$field])) {
                $profileDataChanges[$field] = $changes[$field];
            }
        }
        unset($field);

        if (! empty($profileDataChanges)) {
            if (isset($profileDataChanges['about'])) {
                $purifier =  $this->_sc->get("purifier");
                /** @var $purifier \Ml_Model_HtmlPurifier() */

                $profileDataChanges['about_filtered'] = $purifier->purify($profileDataChanges['about']);
            }
            $profile->addInfo($signedUserInfo['id'], $profileDataChanges);
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

        $pictureInfo = $signedUserInfo["avatar_info"];

        if ($pictureInfo) {
            $this->view->pictureLink = $picture->getImageLink($pictureInfo["id"], $pictureInfo["secret"], "small.jpg");
        }

        if (! $this->_request->isPost() || ! $form->isValid($this->_request->getPost())) {
            return true;
        }

        if ($form->getValue("delete")) {
            $people->update($signedUserInfo["id"], array("avatar_info" => false));
            $oldPicturesInfo = $signedUserInfo["avatar_info"];
            $picture->delete($oldPicturesInfo);
            $this->_redirect($this->_router->assemble(array(), "account_picture"), array("exit"));
        } else if ($form->Image->isUploaded()) {
            $fileInfo = $form->Image->getFileInfo();
            $picturesInfo = $picture->create($fileInfo['Image']['tmp_name'], $signedUserInfo['id']);

            if (is_array($picturesInfo)) {
                $people->update($signedUserInfo['id'], array("avatar_info" => json_encode($picturesInfo)));
                $oldPicturesInfo = $signedUserInfo["avatar_info"];
                if (is_array($oldPicturesInfo) && isset($oldPicturesInfo["secret"])) {
                    $picture->delete($oldPicturesInfo);
                }
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

            $currentAddress["telephone_1_name"] = $currentAddress["phones"][0]["name"];
            $currentAddress["telephone_1"] = mb_substr($currentAddress["phones"][0]["tel"], 3);

            if (isset($currentAddress["phones"][1])) {
                $currentAddress["telephone_2_name"] = $currentAddress["phones"][1]["name"];
                $currentAddress["telephone_2"] = mb_substr($currentAddress["phones"][1]["tel"], 3);
            }

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

        $address["phones"][] = [
            "name" => $values["telephone_1_name"],
            "tel" => "+55" . str_replace(array(" ", "-"), "", $values["telephone_1"])
        ];

        if ($values["telephone_2"]) {
            $address["phones"][] = [
                "name" => $values["telephone_2_name"],
                "tel" => "+55" . str_replace(array(" ", "-"), "", $values["telephone_2"])
            ];
        }

        $people->update($signedUserInfo['id'], array("address" => json_encode($address)));

        $this->_redirect($this->_router->assemble(array(), "account_address"), array("exit"));
    }
}
