<?php

class UserPostManagerController extends Ml_Controller_Action
{
    use Ml_Controller_People;

    public function newAction()
    {
        $signedUserInfo = $this->_registry->get("signedUserInfo");

        $router = Zend_Controller_Front::getInstance()->getRouter();

        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->viewRenderer("new-xhr");
            $this->_helper->layout->disableLayout();
        }

        $posts =  $this->_registry->get("sc")->get("posts");
        /** @var $posts \Ml_Model_Posts() */

        $form = new Ml_Form_UserPostNew(null, $this->_translatePosts);
        $this->view->assign("postForm", $form);

        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $values = $form->getValues();

            $data = [];

            $formKeys = array("type", "make", "model", "price", "model_year", "engine");

            foreach ($formKeys as $key) {
                if (! isset($validatePost[$key])) {
                    if ($key == "price") {
                        $data["price"] = str_replace(array(",", "."), "", $values["price"]);
                    } else {
                        $data[$key] = $values[$key];
                    }
                }
            }

            $data["description"] = $signedUserInfo["post_template"];

            $id = $posts->create($this->_auth->getIdentity(), $data);

            $username = $signedUserInfo["username"];

            $this->_redirect($router->assemble(
                    array("username" => $username, "post_id" => $id), "user_post"), array("exit")
            );
        }
    }

    public function editAction()
    {
        $params = $this->getRequest()->getParams();

        $this->view->addJsParam("route", "user/post-manager");

        $people =  $this->_registry->get("sc")->get("people");
        /** @var $people \Ml_Model_People() */

        $posts =  $this->_registry->get("sc")->get("posts");
        /** @var $posts \Ml_Model_Posts() */

        $validatePost = $this->getRequest()->getPost();

        $post = $this->_post;
        $userInfo = $this->_userInfo;

        $type = $post["type"];

        $availableEquipment = $posts->getAvailableEquipment($type);

        $this->view->assign("availableEquipment", $availableEquipment);

        $form = new Ml_Form_UserPostEdit(
            null,
            $this->_translatePosts,
            $availableEquipment,
            $params["username"],
            $params["post_id"],
            $type
        );

        $form->setDefaults($post);

        $this->view->assign("postForm", $form);

        if ($this->getRequest()->isPost()) {
            $formKeys = ["make", "model", "name", "price", "model_year", "engine", "traction",
                "transmission", "fuel", "km", "armor", "handicapped", "equipment",
                "youtube_video", "description", "status"];

            foreach ($formKeys as $key) {
                if (! isset($validatePost[$key])) {
                    if ($key == "price") {
                        $filterCurrencyBr = new Ml_Filter_CurrencyBr();
                        $validatePost["price"] = $filterCurrencyBr->filter($post["price"] / 100);
                    } else {
                        $validatePost[$key] = $post[$key];
                    }
                }
            }


            if ($form->isValid($validatePost)) {
                $values = $form->getValues();

                $data = [];

                $data["type"] = $type;

                foreach ($formKeys as $key) {
                    $data[$key] = $values[$key];
                }

                $data["price"] = str_replace(array(",", "."), "", $data["price"]);

                $updatedPost = $posts->update($post["id"], $data);

                if (! $updatedPost) {
                    throw new Exception("Error trying to update post");
                }

                $post = $updatedPost;

                $this->_post = $post;

                $this->view->assign("post", $post);
            } else {
                $this->getResponse()->setHttpResponseCode(404);
                $this->_helper->json(["errors" => $form->getErrors()]);
            }
        }

        $this->_helper->layout->disableLayout();

        if ($this->getRequest()->getParam("show-partial") == "post-product-info") {
            $this->render("user-post/partial-product-main-info", null, "user-post");
        } else {
            $this->_helper->json($post);
        }
    }

    public function pictureAddAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $picture =  $this->_sc->get("picture");
        /** @var $picture \Ml_Model_Picture() */

        $posts =  $this->_sc->get("posts");
        /** @var $posts \Ml_Model_Posts() */

        $post = $this->_post;
        $userInfo = $this->_userInfo;

        $numberOfPictures = count($post["pictures"]);

        if ($numberOfPictures >= $posts->getMaxPicturesLimit()) {
            $this->getResponse()->setHttpResponseCode(403);
            $this->_helper->json(["error" => "too-many-pictures"]);
            return;
        }

        $form = new Ml_Form_PicturePost();

        if (! $this->getRequest()->isPost() || ! $form->isValid($this->getRequest()->getPost())) {
            $this->getResponse()->setHttpResponseCode(404);
            $this->_helper->json(["error" => ["validate" => $form->getErrors()]]);
            return;
        }

        if ($form->Filedata->isUploaded()) {
            $fileInfo = $form->Filedata->getFileInfo();

            $pictureId = $picture->create($fileInfo['Filedata']['tmp_name'], $userInfo["id"], $post["id"]);

            if (! $pictureId) {
                $this->getResponse()->setHttpResponseCode(404);
                $this->_helper->json(["error" => "not-uploaded"]);
                return;
            }

            $pictureInfo = $picture->getInfo($pictureId);

            $posts->syncSearch($post["id"]);

            $this->_helper->json($pictureInfo);
        }
    }

    public function pictureDeleteAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $posts =  $this->_registry->get("sc")->get("posts");
        /** @var $posts \Ml_Model_Posts() */

        $picture =  $this->_sc->get("picture");
        /** @var $picture \Ml_Model_Picture() */

        $post = $this->_post;
        $userInfo = $this->_userInfo;

        $form = new Ml_Form_PicturePostDelete();

        if (! $this->getRequest()->isPost() || ! $form->isValid($this->getRequest()->getPost())) {
            $this->getResponse()->setHttpResponseCode(403);
            $this->_helper->json(["error" => ["validate" => $form->getErrors()]]);
            return;
        }

        $pictureId = $form->getValue("picture_id");

        $picture->delete($pictureId, $userInfo["id"], $post["id"]);

        $posts->syncSearch($post["id"]);

        $this->_helper->json(["pictureRemoved" => $pictureId]);
        return;
    }

    public function pictureEditAction() {
        $userInfo = $this->_userInfo;

        $picture =  $this->_sc->get("picture");
        /** @var $picture \Ml_Model_Picture() */

        $form = new Ml_Form_PicturePostEdit();

        if (! $this->getRequest()->isPost() || ! $form->isValid($this->getRequest()->getPost())) {
            $this->getResponse()->setHttpResponseCode(403);
            $this->_helper->json(["error" => ["validate" => $form->getErrors()]]);
            return;
        }

        $values = $form->getValues();

        $pictureInfo = $picture->getInfo($values["picture_id"]);

        if (! is_array($pictureInfo)) {
            $this->getResponse()->setHttpResponseCode(403);
            $this->_helper->json(["error" => ["picture" => "not-found"]]);
            return;
        }

        if ($pictureInfo["uid"] !== $userInfo["id"]) {
            $this->getResponse()->setHttpResponseCode(403);
            $this->_helper->json(["error" => ["permissions" => "different-picture-owner"]]);
            return;
        }

        $newOptions = [
            "x" => $values["x"],
            "y" => $values["y"],
            "x2" => $values["x2"],
            "y2" => $values["y2"],
            "w" => $values["w"],
            "h" => $values["h"]
        ];

        $saved = $picture->setOptions($pictureInfo["picture_id"], $newOptions);

        $this->_helper->json($saved);
    }

    public function pictureSortAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $posts =  $this->_registry->get("sc")->get("posts");
        /** @var $posts \Ml_Model_Posts() */

        $picture =  $this->_sc->get("picture");
        /** @var $picture \Ml_Model_Picture() */

        $post = $this->_post;
        $userInfo = $this->_userInfo;

        $form = new Ml_Form_Hash();

        if (! $this->getRequest()->isPost() || ! $form->isValid($this->getRequest()->getPost())) {
            $this->getResponse()->setHttpResponseCode(403);
            $this->_helper->json(["error" => ["validate" => $form->getErrors()]]);
            return;
        }

        $unsafePostData = $this->getRequest()->getPost();

        if (isset($unsafePostData["picture_id"]) && is_array($unsafePostData["picture_id"])) {
            $safePictureIds = [];

            $stringLengthValidator = new Ml_Validate_StringLength(["min" => 1, "max" => 64]);
            $regexValidator = new Zend_Validate_Regex("/^[\w\-]+$/");
            foreach ($unsafePostData["picture_id"] as $pictureId) {
                if (
                    $stringLengthValidator->isValid($pictureId) &&
                    $regexValidator->isValid($pictureId)
                ) {
                    $safePictureIds[] = $pictureId;
                }
            }

            $picturesInfo = $posts->sortPictures($post["id"], $safePictureIds);
            $this->_helper->json($picturesInfo);
            return;
        }

        $this->getResponse()->setHttpResponseCode(404);
    }
}
