<?php

class Ml_Plugins_LoginRedirect extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $request->setModuleName("default")
            ->setControllerName("login")
            ->setActionName("redirect")
            ->setParams(array("module" => "default", "controller" => "login", "action" => "redirect"));
            
    }
}
