<?php

/**
 * Index Controller
 *
 *
 * @copyright  2008 Henrique Vicente
 * @since      File available since Release 0.1
*/

class IndexController extends Ml_Controller_Action
{
    public function indexAction()
    {
        $this->view->assign("config", $this->_config);
        $this->view->assign("auth", $this->_auth);
        $this->_forward("index", "search");
    }
}
