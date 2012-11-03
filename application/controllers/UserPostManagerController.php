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

}
