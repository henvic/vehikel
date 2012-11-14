<?php

class UserPostManagerController extends Ml_Controller_Action
{
    use Ml_Controller_People;

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

        $form = new Ml_Form_PicturePostDelete();

        if (! $this->getRequest()->isPost() || ! $form->isValid($this->getRequest()->getPost())) {
            $this->getResponse()->setHttpResponseCode(403);
            var_dump($form->getErrors());
            return;
        }

        $pictureId = $form->getValue("picture_id");

        foreach ($post["pictures"] as $position => $eachPicture) {
            if ($eachPicture["id"] == $pictureId) {
                $posts->deletePicture($this->_post["id"], $pictureId);
                $picture->delete($eachPicture["id"], $eachPicture["secret"]);
                break;
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

        $form = new Ml_Form_Hash();

        if (! $this->getRequest()->isPost() || ! $form->isValid($this->getRequest()->getPost())) {
            $this->getResponse()->setHttpResponseCode(403);
            $this->_helper->json(["error" => ["validate" => $form->getErrors()]]);
            return;
        }

        $unsafePostData = $this->getRequest()->getPost();

        if (isset($unsafePostData["picture_id"]) && is_array($unsafePostData["picture_id"])) {
            $safePictureIds = [];

            $stringLengthValidator = new Ml_Validate_StringLength(["min" => 1, "max" => 20]);
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
