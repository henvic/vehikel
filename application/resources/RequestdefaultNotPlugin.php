<?php
/**
 * This is NOT a plugin
 * It is here just for modularization
 * 
 * It should be loaded for default an API modules
 * 
 */

require 'RequestNotPlugin.php';
$router->addConfig($routerConfig, "routes");

$frontController->registerPlugin(new Ml_Plugins_ReservedUsernames());
Zend_Controller_Action_HelperBroker::getStaticHelper("Redirector")->setPrependBase(false);
$registry = Zend_Registry::getInstance();
$config = $registry->get("config");
$frontController->setBaseUrl($config['webroot']);


$this->registerPluginResource("Mysession");
$this->registerPluginResource("Myview");

$loader = new Zend_Loader_PluginLoader();

$loader->addPrefixPath('Zend_View_Helper', EXTERNAL_LIBRARY_PATH . '/Zend/View/Helper/')
       ->addPrefixPath('Ml_View_Helper', APPLICATION_PATH . '/views/helpers');

$classFileIncCache = CACHE_PATH . '/pluginDefaultLoaderCache.php';
if (file_exists($classFileIncCache)) {
    require $classFileIncCache;
}

Zend_Loader_PluginLoader::setIncludeFileCache($classFileIncCache);

$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
$viewRenderer->initView();
