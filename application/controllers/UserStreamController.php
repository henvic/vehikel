<?php

class UserStreamController extends Ml_Controller_Action
{
    use Ml_Controller_People;

    public function indexAction()
    {
        $people =  $this->_sc->get("people");
        /** @var $people \Ml_Model_People() */

        $this->view->addJsParam("route", "user/stream");

        $params = $this->getRequest()->getParams();
        
        $userInfo = $this->_userInfo;

        if (isset($params["format"]) && $params["format"] == "json") {
            $this->_helper->json($people->getPublicInfo($userInfo));
        }

        $this->_helper->viewStyle();

        $posts =  $this->_sc->get("posts");
        /** @var $posts \Ml_Model_Posts() */

        $search =  $this->_sc->get("search");
        /** @var $search \Ml_Model_Search() */

        $picture =  $this->_sc->get("picture");
        /** @var $picture \Ml_Model_Picture() */

        $this->view->assign("facetsQuery", $search->getFacetsQuery());

        $page = $this->_request->getUserParam("page");

        $types = $posts->getTypes();

        $statuses = $posts->getStatuses();

        if (isset($params["type"]) && in_array($params["type"], $types)) {
            $type = $params["type"];
        } else {
            $type = "";
        }

        if (isset($params["make"]) && is_string($params["make"]) && mb_strlen($params["make"]) <= 30) {
            $make = $params["make"];
        } else {
            $make = false;
        }

        if (isset($params["model"]) && is_string($params["model"]) && mb_strlen($params["model"]) <= 30) {
            $model = $params["model"];
        } else {
            $model = false;
        }

        if ($this->_auth->getIdentity() == $userInfo["id"] &&
            isset($params["status"]) && in_array($params["status"], $statuses)) {
            $status = $params["status"];
        } else {
            $status = Ml_Model_Posts::STATUS_ACTIVE;
        }

        $stock = $posts->getStockAmountByUserId($userInfo["id"], $type, $status);

        $this->view->addJsParam("postUid", $userInfo["id"]);
        $this->view->addJsParam("postUsername", $userInfo["username"]);

        $this->view->assign("type", $type);
        $this->view->assign("make", $make);
        $this->view->assign("model", $model);
        $this->view->assign("status", $status);
        $this->view->assign("stock", $stock);

        $this->view->addJsParam("status", $status);

        $paginator = $posts->getUserStreamPage($userInfo['id'], 10, $page, $type, $make, $model, $status);

        $postsPictures = [];

        foreach ($paginator->getCurrentItems() as $post) {
            $pictures = $picture->getPictures($userInfo["id"], $post["id"], Ml_Model_Picture::PICTURE_ACTIVE);
            $postsPictures[$post["id"]] = $pictures;
        }

        //Test if there is enough pages or not
        if (! $this->_helper->pageExists($paginator)) {
            $this->_redirect($this->_router->assemble(array("username" =>
            $userInfo['username']), "user_stream_1stpage"), array("exit"));
        }

        $this->view->assign("posts", $paginator);
        $this->view->assign("postsPictures", $postsPictures);
    }
}
