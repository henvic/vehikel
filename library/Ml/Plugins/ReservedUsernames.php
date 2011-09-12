<?php

class Ml_Plugins_ReservedUsernames extends Zend_Controller_Plugin_Abstract
{
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        $params = $request->getParams();
        
        //avoid rendering /index.php/... 10 = letters in index.php/
        if (mb_substr($_SERVER['REQUEST_URI'], 0, 
        10 + mb_strlen($config['webroot'])) == $config['webroot'] . '/index.php') {
            $request->setControllerName('doesnotexists')
                ->setActionName('withindexdotphp');
        }
        
        //@todo this is a workaround, the better approach was to get the string 'as it is'
        if (isset($params['username']) &&
         mb_strstr($_SERVER['REQUEST_URI'], "?", true) != 'proxy') {
            require APPLICATION_PATH . "/configs/reserved-usernames.php";
            
            if (in_array($params['username'], $reservedUsernames)) {
                $request->setControllerName('static')
                ->setActionName('docs');
            }
        }
    }
}