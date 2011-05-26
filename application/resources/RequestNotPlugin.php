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

require_once 'ML/RouteModule.php';

$compat = new ML_Controller_Router_Route_Module(array(), $dispatcher, $request);
$router->addRoute(HOST_MODULE, $compat);

$routerConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/'.HOST_MODULE.'Routes.ini');
