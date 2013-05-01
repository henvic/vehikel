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

        if (isset($validatePost["type"])) {
            switch ($validatePost["type"]) {
                case "car" :
                    $type = "car";
                    break;
                case "motorcycle" :
                    $type = "motorcycle";
                    break;
                case "boat" :
                    $type = "boat";
                    break;
                default:
                    $this->getResponse()->setHttpResponseCode(404);
                    $this->_helper->json(["errors" => array("type" => "invalid")]);
            }
        }

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

            // clear the traction, transmission, and equipment on type change
            if ($type != $post["type"]) {
                $validatePost["traction"] = "";
                $validatePost["transmission"] = "";
                $validatePost["equipment"] = [];
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

            $id = $userInfo['id'] . "-p-" . $post["id"] . "-" . mt_rand();

            $pictureInfo = $picture->create($fileInfo['Filedata']['tmp_name'], $id);

            if (is_array($pictureInfo)) {
                $posts->addPicture($pictureInfo, $post["id"]);

                $this->_helper->json($pictureInfo);
            } else {
                $this->getResponse()->setHttpResponseCode(404);
                $this->_helper->json(["error" => "not-uploaded"]);
                return;
            }
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

        if (is_array($post["pictures"])) {
            foreach ($post["pictures"] as $position => $eachPicture) {
                if ($eachPicture["id"] == $pictureId) {
                    $posts->deletePicture($this->_post["id"], $pictureId);
                    $picture->delete($eachPicture["id"], $eachPicture["secret"]);
                    break;
                }
            }
        }

        $this->_helper->json(["pictureRemoved" => $pictureId]);
        return;
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
