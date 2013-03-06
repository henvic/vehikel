<?php

class SearchController extends Ml_Controller_Action
{
    public function indexAction()
    {
        $this->view->addJsParam("route", "search/engine");

        $this->_helper->viewStyle();
    }
}
