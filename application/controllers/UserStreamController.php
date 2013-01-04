<?php

class UserStreamController extends Ml_Controller_Action
{
    use Ml_Controller_People;

    public function indexAction()
    {
        $this->view->addJsParam("route", "user/stream");

        $params = $this->getRequest()->getParams();
        
        $userInfo = $this->_userInfo;

        $postsViewStyleNamespace = new Zend_Session_Namespace("posts-view-style");

        if (isset($params["posts_view_style"])) {
            if ($params["posts_view_style"] == 'table') {
                $postsViewStyleNamespace->table = true;
            } else {
                $postsViewStyleNamespace->table = false;
            }
        }

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

        $this->view->assign("type", $type);
        $this->view->assign("status", $status);

        $paginator = $posts->getUserStreamPage($userInfo['id'], 10, $page, $type, $status);

        //Test if there is enough pages or not
        if (! $this->_helper->pageExists($paginator)) {
            $this->_redirect($this->_router->assemble(array("username" =>
            $userInfo['username']), "user_stream_1stpage"), array("exit"));
        }

        $this->view->assign("postsViewStyleIsTable", $postsViewStyleNamespace->table);

        $this->view->assign("posts", $paginator);

        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();

            $this->render("partial");
        }
    }
}
