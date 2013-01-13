<?php

class SearchController extends Ml_Controller_Action
{
    public function indexAction()
    {
        $params = $this->getRequest()->getParams();

        $this->view->addJsParam("route", "search/engine");

        $postsViewStyleNamespace = new Zend_Session_Namespace("posts-view-style");

        if (isset($params["posts_view_style"])) {
            if ($params["posts_view_style"] == 'table') {
                $postsViewStyleNamespace->table = true;
            } else {
                $postsViewStyleNamespace->table = false;
            }
        }

        $this->view->assign("postsViewStyleIsTable", $postsViewStyleNamespace->table);
    }
}
