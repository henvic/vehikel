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

        $page = $this->_request->getUserParam("page");

        $types = $posts->getTypes();

        $statuses = $posts->getStatuses();

        if (isset($params["type"]) && in_array($params["type"], $types)) {
            $type = $params["type"];
        } else {
            $type = "";
        }

        if ($this->_auth->getIdentity() == $userInfo["id"] &&
            isset($params["status"]) && in_array($params["status"], $statuses)) {
            $status = $params["status"];
        } else {
            $status = Ml_Model_Posts::STATUS_ACTIVE;
        }

        $stock = $posts->getStockAmountByUserId($userInfo["id"], $type, $status);

        $this->view->assign("type", $type);
        $this->view->assign("status", $status);
        $this->view->assign("stock", $stock);

        $paginator = $posts->getUserStreamPage($userInfo['id'], 10, $page, $type, $status);

        //Test if there is enough pages or not
        if (! $this->_helper->pageExists($paginator)) {
            $this->_redirect($this->_router->assemble(array("username" =>
            $userInfo['username']), "user_stream_1stpage"), array("exit"));
        }

        $this->view->assign("posts", $paginator);
    }
}
