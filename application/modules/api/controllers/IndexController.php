<?php

class IndexController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        $webHost = htmlentities($config['webhost'], ENT_QUOTES, "UTF-8");
        
        echo "This is only the end-point for the API (Application Programming Interface) methods. If you are looking for the documentation try http://".$webHost."/api instead.";exit();
    }
}