<?php

class UserStreamController extends Ml_Controller_Action
{
    use Ml_Controller_People;

    public function indexAction()
    {
        $this->view->addJsParam("route", "user/stream");

        $postsViewStyleNamespace = new Zend_Session_Namespace("posts-view-style");

        $params = $this->getRequest()->getParams();

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

        $paginator = $posts->getUserStreamPage($this->_userInfo['id'], 10, $page);

        //Test if there is enough pages or not
        if (! $this->_helper->pageExists($paginator)) {
            $this->_redirect($this->_router->assemble(array("username" =>
            $this->_userInfo['username']), "user_stream_1stpage"), array("exit"));
        }

        $this->view->postsViewStyleIsTable = $postsViewStyleNamespace->table;

        $this->view->posts = $paginator;

        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();

            $this->render("partial");
        }
    }
}
