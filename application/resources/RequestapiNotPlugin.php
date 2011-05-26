<?php
/**
 * This is NOT a plugin
 * It is here just for modularization
 * 
 * It should be loaded for default an API modules
 * 
 */

require_once 'RequestNotPlugin.php';
$router->addConfig($routerConfig, "apiroutes");

$this->registerPluginResource("Api");
$this->unregisterPluginResource("session");
$this->unregisterPluginResource("view");
$this->unregisterPluginResource("layout");
$registry = Zend_Registry::getInstance();
$config = $registry->get("config");
$frontController->setBaseUrl($config->apiroot);
$frontController->setParam('noViewRenderer', true);
$frontController->addModuleDirectory(APPLICATION_PATH.'/modules'); 
$frontController->addControllerDirectory(APPLICATION_PATH.'/modules/'.HOST_MODULE.'/controllers');

$response = new Zend_Controller_Response_Http;

if(isset($_GET['response_format']) && $_GET['response_format'] == 'json') $content_type = 'application/json';
else $content_type = 'text/xml';

$response->setHeader('Content-Type', $content_type.'; charset=utf-8', true);
$frontController->setResponse($response);
