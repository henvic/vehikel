<?php
/**
 * This is NOT a plugin
 * It is here just for modularization
 * 
 * It should be loaded for default an API modules
 * 
 */

Zend_Controller_Action_HelperBroker::addPath(APPLICATION_PATH .'/controllers/helpers');

$this->bootstrap('FrontController');
$frontController = $this->getResource('FrontController');

$dispatcher = $frontController->getDispatcher();
$request = $frontController->getRequest();

$router = $frontController->getRouter();
$router->removeDefaultRoutes();

require LIBRARY_PATH . '/ML/RouteModule.php';
$compat = new ML_Controller_Router_Route_Module(array(), $dispatcher, $request);
$router->addRoute(HOST_MODULE, $compat);


function getCachedIni($file)//don't use this for the Zend_Application
{
    $registry = Zend_Registry::getInstance();
    
    $sysCache = $registry->get("sysCache");
    
    $configMTime = filemtime($file);
    
    $cacheId = "cachedIni_" . md5($file);
    
    $cacheLastMTime = $sysCache->test($cacheId);
    
    if ($cacheLastMTime !== false && $configMTime <= $cacheLastMTime) {
        return $sysCache->load($cacheId, true);
    }
    
    $routerConfig = new Zend_Config_Ini($file);
    $sysCache->save($routerConfig, $cacheId, array(), null);
    
    return $routerConfig;
    
}

$routerConfig = getCachedIni(APPLICATION_PATH . '/configs/'.HOST_MODULE.'Routes.ini');
