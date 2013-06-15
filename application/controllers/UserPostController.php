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

        $picture =  $this->_registry->get("sc")->get("picture");
        /** @var $picture \Ml_Model_Picture() */

        $galleryImages = [];

        if (is_array($post["pictures"])) {
            foreach ($post["pictures"] as $postPicture) {
                $opt = "";

                $opt .= $picture->getCropOptions($postPicture["options"]);

                $optThumbnail = $opt . "/266x200";
                $imageThumbnail = $picture->getImageLink($postPicture["picture_id"], $optThumbnail);

                $imageMedium = $opt . "/800x600";
                $image = $picture->getImageLink($postPicture["picture_id"], $imageMedium);

                $imageLarge = $picture->getImageLink($postPicture["picture_id"], $opt);

                $pictureData = [
                    "id" => $postPicture["picture_id"],
                    "type" => "image",
                    "thumb" => $imageThumbnail,
                    "image" => $image,
                    "big" => $imageLarge
                ];

                if ($this->_editable) {
                    $pictureData["original"] = $picture->getImageLink($postPicture["picture_id"]);
                    $pictureData["crop_options"] = $postPicture["options"];

                    $pictureMeta = $postPicture["meta"];

                    if (isset($pictureMeta["thumbor"]) && isset($pictureMeta["thumbor"]["source"])) {
                        if (isset($pictureMeta["thumbor"]["source"]["width"])) {
                            $pictureData["original_size"]["width"] = (int) $pictureMeta["thumbor"]["source"]["width"];
                        }

                        if (isset($pictureMeta["thumbor"]["source"]["height"])) {
                            $pictureData["original_size"]["height"] = (int) $pictureMeta["thumbor"]["source"]["height"];
                        }
                    }
                }

                $galleryImages[] = $pictureData;
            }

            if (empty($galleryImages)) {
                $pictureData = [
                    "id" => $picture->getPlaceholder(),
                    "type" => "image",
                    "thumb" => $picture->getImageLink($picture->getPlaceholder()),
                    "image" => $picture->getImageLink($picture->getPlaceholder()),
                    "placeholder" => true
                ];

                $galleryImages[] = $pictureData;
            }
        }

        if (! empty($post["youtube_video"])) {
            $escapedYoutubeVideo = rawurlencode($post["youtube_video"]);

            $video = [
                "id" => $post["youtube_video"],
                "type" => "video",
                "iframe" => "http://www.youtube.com/embed/" . $escapedYoutubeVideo . "?wmode=opaque",
                "thumb" => "http://img.youtube.com/vi/" . $escapedYoutubeVideo . "/default.jpg"
            ];
            $galleryImages[] = $video;
        }

        if (isset($params["format"]) && $params["format"] == "json") {
            $content = $posts->getPublicInfo($post);
            $content["user"] = $people->getPublicInfo($userInfo);

            //@todo fix this hack, pass the gallery data using a better approach
            if (isset($params["gallery"])) {
                $content["gallery"] = $galleryImages;
            }

            $this->_helper->json($content);
        }

        $this->view->assign("maxPicturesLimit", $posts->getMaxPicturesLimit());
        $this->view->assign("config", $this->_config);

        $this->view->addJsParam("route", "user/post");
        $this->view->addJsParam("postId", $post["id"]);
        $this->view->addJsParam("postUid", $userInfo["id"]);
        $this->view->addJsParam("postUsername", $userInfo["username"]);
        $this->view->addJsParam("postGalleryImages", $galleryImages);

        $availableEquipment = $posts->getAvailableEquipment($post["type"]);

        $this->view->assign("availableEquipment", $availableEquipment);

        if ($this->_editable) {
            $form = new Ml_Form_UserPostEdit(
                null,
                $this->_translatePosts,
                $availableEquipment,
                $params["username"],
                $params["post_id"],
                $post["type"]
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
