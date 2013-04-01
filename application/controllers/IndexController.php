<?php

/**
 * Index Controller
 *
 *
 * @copyright  2008 Henrique Vicente
 * @since      File available since Release 0.1
*/

class IndexController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $this->_forward("index", "search");
    }
}
