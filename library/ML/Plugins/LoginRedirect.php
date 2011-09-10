<?php

class Ml_Plugins_LoginRedirect extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch($request)
    {
        $request->setModuleName('default')
            ->setControllerName('login')
            ->setActionName('redirect');
    }
}
