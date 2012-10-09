<?php

trait Ml_Controller_People
{
    protected $_userInfo;

    protected $_post;

    public function preDispatch()
    {
        $people =  $this->_registry->get("sc")->get("people");
        /** @var $people \Ml_Model_People() */

        $username = $this->_request->getUserParam("username");

        // first, check if the username is reserved. If it is it should go to the default route controller/action

        $usernameReservedValidate = new Ml_Validate_UsernameNotReserved(
            APPLICATION_PATH . "/configs/reserved-usernames.json"
        );

        $isUsernameNotReserved = $usernameReservedValidate->isValid($username);

        if (! $isUsernameNotReserved) {
            return $this->_forward("docs", "static");
        }

        // if it is not reserved, just continue the flow

        $userInfo = $people->getByUsername($username);

        if (! $userInfo) {
            return $this->_forward("not-found", "error", "default", array("error" => "user-not-found"));
        }

        if (! $userInfo["active"]) {
            return $this->_forward("not-found", "error", "default", array("error" => "user-is-no-longer-active"));
        }

        $this->_userInfo = $userInfo;
        $this->view->userInfo = $userInfo;

        $postId = $this->_request->getUserParam("post_id");

        if ($postId) {
            $posts =  $this->_registry->get("sc")->get("posts");
            /** @var $posts \Ml_Model_Posts() */

            $post = $posts->getById($postId);

            if (! $post) {
                return $this->_forward("not-found", "error", "default", array("error" => "post-not-found"));
            }

            if ($post["uid"] != $userInfo["id"]) {
                return $this->_forward("not-found", "error", "default", array("error" => "post-and-user-mismatch"));
            }

            if (! $post["active"]) {
                return $this->_forward("not-found", "error", "default", array("error" => "post-is-no-longer-active"));
            }

            $this->_post = $post;
            $this->view->post = $post;
        }

        $translatePosts = new Zend_Translate(
            array(
                "adapter" => "array",
                "content" => APPLICATION_PATH . "/languages/pt_BR/Posts.php",
                "locale" => "pt_BR",
                "tag" => "vehicle"
            )
        );

        $this->view->translatePosts = $translatePosts->getAdapter();
    }
}
