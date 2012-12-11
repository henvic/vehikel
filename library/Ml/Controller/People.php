<?php

trait Ml_Controller_People
{
    protected $_userInfo;

    protected $_post;

    protected $_editable;

    protected $_translatePosts;

    public function preDispatch()
    {
        $translatePosts = new Zend_Translate(
            array(
                "adapter" => "array",
                "content" => APPLICATION_PATH . "/languages/pt_BR/Posts.php",
                "locale" => "pt_BR",
                "tag" => "vehicle"
            )
        );

        $this->_translatePosts = $translatePosts;

        $this->view->assign("translatePosts", $translatePosts->getAdapter());

        $route = Zend_Controller_Front::getInstance()->getRouter()->getCurrentRouteName();

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

            switch ($route) {
                case "user_post_edit" :
                case "user_post_delete" :
                case "user_post_picture_add" :
                case "user_post_picture_delete" :
                case "user_post_picture_sort" :
                if ($this->_auth->getIdentity() != $post["uid"]) {
                    $this->getResponse()->setHttpResponseCode(403);
                    return $this->_forward(
                        "not-found", "error", "default", array("error" => "post-is-no-longer-active")
                    );
                }
            }

            if ($post["status"] != Ml_Model_Posts::STATUS_ACTIVE && $userInfo["id"] != $this->_auth->getIdentity()) {
                return $this->_forward("not-found", "error", "default", array("error" => "post-is-no-longer-active"));
            }

            if ($this->_auth->getIdentity() == $post["uid"]) {
                $editable = true;
            } else {
                $editable = false;
            }

            $this->_editable = $editable;
            $this->view->editable = $editable;
            $this->view->addJsParam("postEditable", $editable);

            $this->_post = $post;
            $this->view->post = $post;
        }
    }
}
