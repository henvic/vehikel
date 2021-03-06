<?php

class UserPostManagerController extends Ml_Controller_Action
{
    use Ml_Controller_People;

    public function newAction()
    {
        $signedUserInfo = $this->_registry->get("signedUserInfo");

        $router = Zend_Controller_Front::getInstance()->getRouter();

        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->view->isModal = true;
            $this->_helper->layout->disableLayout();
        }

        $posts =  $this->_registry->get("sc")->get("posts");
        /** @var $posts \Ml_Model_Posts() */

        $form = new Ml_Form_UserPostNew(null, $this->_translatePosts);
        $this->view->assign("postForm", $form);

        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $values = $form->getValues();

            $data = [];

            $formKeys = [
                "type", "make", "model", "price", "model_year", "engine", "km", "fuel", "transmission"
            ];

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

    public function openAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->view->isModal = true;
            $this->_helper->layout->disableLayout();
        }
    }

    public function openIdAction()
    {
        $params = $this->getRequest()->getParams();

        $people =  $this->_registry->get("sc")->get("people");
        /** @var $people \Ml_Model_People() */

        $posts =  $this->_registry->get("sc")->get("posts");
        /** @var $posts \Ml_Model_Posts() */

        if (! $this->_auth->hasIdentity()) {
            return $this->_forward("redirect", "login");
        }

        $post = $posts->getById($params["id"]);

        $router = Zend_Controller_Front::getInstance()->getRouter();

        $this->view->addJsParam("route", "user/open-id");

        if (!$post) {
            $this->render("open-id-not-found");
            return;
        }

        $userInfo = $people->getById($post["uid"]);

        if (! $userInfo) {
            throw new Exception("User not found for the searched post");
        }

        if ($this->_auth->getIdentity() != $post["uid"]) {
            $this->_redirect($router->assemble(
                    ["username" => $userInfo["username"], "post_id" => $post["id"]], "user_post"), ["exit"]
            );
            exit;
        }

        $this->view->addJsParam("postId", $post["id"]);
        $this->view->addJsParam("postUid", $userInfo["id"]);
        $this->view->addJsParam("postUsername", $userInfo["username"]);

        $this->view->userInfo = $userInfo;
        $this->view->post = $post;
    }

    public function editAction()
    {
        $params = $this->getRequest()->getParams();

        $people =  $this->_registry->get("sc")->get("people");
        /** @var $people \Ml_Model_People() */

        $posts =  $this->_registry->get("sc")->get("posts");
        /** @var $posts \Ml_Model_Posts() */

        $validatePost = $this->getRequest()->getPost();

        $post = $this->_post;

        $type = $post["type"];

        $availableEquipment = $posts->getAvailableEquipment($type);

        $form = new Ml_Form_UserPostEdit(
            null,
            $this->_translatePosts,
            $availableEquipment,
            $params["username"],
            $params["post_id"],
            $type
        );

        $form->setDefaults($post);

        if ($this->getRequest()->isPost()) {

            if ($form->isValid($validatePost)) {
                $values = $form->getValues();

                //unset / ignore the values of the form inputs not submitted
                $listOfValuesSent = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);
                $data = array_intersect_key($values, $listOfValuesSent);

                if (isset($data["price"])) {
                    $data["price"] = str_replace(array(",", "."), "", $data["price"]);
                }

                if (isset($data["km"]) && $data["km"] === "") {
                    $data["km"] = null;
                }

                unset($data["hash"]);

                $updatedPost = $posts->update($post["id"], $data);

                if (! $updatedPost) {
                    throw new Exception("Error trying to update post");
                }

                $post = $updatedPost;
            } else {
                $this->getResponse()->setHttpResponseCode(404);
                $this->_helper->json(["errors" => $form->getErrors()]);
            }
        }

        $this->_helper->layout->disableLayout();

        $this->_helper->json($post);
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

            $posts->syncSearchById($post["id"]);

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

        $posts->syncSearchById($post["id"]);

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
            $posts->syncSearchById($post["id"]);
            $this->_helper->json($picturesInfo);
            return;
        }

        $this->getResponse()->setHttpResponseCode(404);
    }
}
