<?php

class UserPostController extends Ml_Controller_Action
{
    use Ml_Controller_People;

    public function indexAction()
    {
        $params = $this->getRequest()->getParams();

        $userInfo = $this->_userInfo;
        $post = $this->_post;

        $people =  $this->_registry->get("sc")->get("people");
        /** @var $people \Ml_Model_People() */

        $posts =  $this->_registry->get("sc")->get("posts");
        /** @var $posts \Ml_Model_Posts() */

        if (isset($params["format"]) && $params["format"] == "json") {
            $content = $posts->getPublicInfo($post);
            $content["user"] = $people->getPublicInfo($userInfo);

            $this->_helper->json($content);
        }

        $this->view->assign("maxPicturesLimit", $posts->getMaxPicturesLimit());

        $this->view->addJsParam("route", "user/post");
        $this->view->addJsParam("postId", $post["id"]);
        $this->view->addJsParam("postUid", $userInfo["id"]);
        $this->view->addJsParam("postUsername", $userInfo["username"]);

        $availableEquipment = $posts->getAvailableEquipment($post["type"]);

        $this->view->assign("availableEquipment", $availableEquipment);

        if ($this->_editable) {
            $form = new Ml_Form_UserPostEdit(
                null,
                $this->_translatePosts,
                $availableEquipment,
                $params["username"],
                $params["post_id"]
            );

            $postForm = $post;

            // divide by 100 because of the cents
            $postForm["price"] = $postForm["price"] / 100;

            $form->setDefaults($postForm);
            $this->view->postForm = $form;
        }

        $form = new Ml_Form_ContactSeller(null, $this->_userInfo["username"], $this->_post["id"]);

        $this->view->assign("contactSellerForm", $form);

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

                $this->view->assign("contact", $values);

                $mail = new Zend_Mail('UTF-8');

                $mail->setBodyText($this->view->render("user-post/contact-message.phtml"))
                    ->addTo($userInfo['email'], $userInfo['name'])
                    ->setSubject('Proposta')
                    ->setReplyTo($values["email"])
                    ->send();

                $this->view->assign("proposalSent", true);
            }
        }

        // out of the isPost because we might want to use GET sometimes
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
            $this->render("partial-contact");
        }
    }
}
