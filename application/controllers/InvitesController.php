<?php

class InvitesController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $auth = Zend_Auth::getInstance();
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        if (! $auth->hasIdentity()) {
            $this->_redirect($router->assemble(array(), "index"), array("exit"));
        }
    }
}
