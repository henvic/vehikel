<?php

class SearchController extends Ml_Controller_Action
{
    public function indexAction()
    {
        $this->view->addJsParam("route", "search/engine");

        $this->_helper->viewStyle();

        $currentRoute = $this->getFrontController()->getRouter()->getCurrentRouteName();

        if ($currentRoute === "index") {
            $indexContent = $this->view->render("index/index.phtml");

            $this->view->assign("indexContent", $indexContent);
        }
    }
}
