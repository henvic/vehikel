<?php

class FaqController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $this->_redirect($this->view->StaticUrl("/help"), array("exit"));
    }
}