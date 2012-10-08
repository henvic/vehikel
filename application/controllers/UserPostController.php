<?php

class UserPostController extends Ml_Controller_Action
{
    use Ml_Controller_People;

    public function indexAction()
    {
        $userInfo = $this->_userInfo;

        $this->view->addJsParam("route", "user/post");

        $people =  $this->_registry->get("sc")->get("people");
        /** @var $people \Ml_Model_People() */

        $form = new Ml_Form_ContactSeller(null, $this->_userInfo["username"], $this->_post["id"]);

        $this->view->contactSellerForm = $form;

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $contactSeller =  $this->_registry->get("sc")->get("contactSeller");
                /** @var $contactSeller \Ml_Model_ContactSeller() */

                $values = $form->getValues();

                $contactSeller->saveMessage(
                    $userInfo,
                    $values["name"],
                    $values["email"],
                    $values["phone"],
                    $values["message"]
                );

                $this->view->contact = $values;

                $mail = new Zend_Mail('UTF-8');

                $mail->setBodyText($this->view->render("user-post/contact-message.phtml"))
                    ->addTo($userInfo['email'], $userInfo['name'])
                    ->setSubject('Proposta')
                    ->setReplyTo($values["email"])
                    ->send();

                $this->view->proposalSent = true;
            }
        }

        // out of the isPost because we might want to use GET sometimes
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
            $this->render("partial-contact");
        }
    }
}
