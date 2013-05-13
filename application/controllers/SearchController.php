<?php

class SearchController extends Ml_Controller_Action
{
    public function indexAction()
    {
        $picture =  $this->_sc->get("picture");
        /** @var $picture \Ml_Model_Picture() */

        $this->view->addJsParam("route", "search/engine");
        $this->view->addJsParam("placeholder", $picture->getPlaceholder());

        $this->_helper->viewStyle();

        $currentRoute = $this->getFrontController()->getRouter()->getCurrentRouteName();

        if ($currentRoute === "index") {
            $indexContent = $this->view->render("index/index.phtml");

            $this->view->assign("indexContent", $indexContent);
        }
    }
}
