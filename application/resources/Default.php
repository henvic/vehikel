<?php
class Ml_Resource_Default extends Zend_Application_Resource_ResourceAbstract
{
    public function init ()
    {
        $registry = Zend_Registry::getInstance();
        
        $config = $registry->get("config");
        $sysCache = $registry->get("sysCache");
        
        $cacheFiles = new Ml_Cache_Files($sysCache);
        
        Zend_Controller_Action_HelperBroker::addPath(APPLICATION_PATH . '/controllers/helpers');
        
        $frontController = $this->getBootstrap()->getResource('FrontController');
        
        $dispatcher = $frontController->getDispatcher();
        $request = $frontController->getRequest();
        
        $router = $frontController->getRouter();

        $compat = new Zend_Controller_Router_Route('*');
        
        $router->addRoute("default", $compat);
        
        $routerConfig = $cacheFiles->getConfigIni(APPLICATION_PATH . '/configs/' . HOST_MODULE . 'Routes.ini');
        
        $router->addConfig($routerConfig, "routes");

        $router->removeDefaultRoutes();
        
        Zend_Controller_Action_HelperBroker::getStaticHelper("Redirector")->setPrependBase(false);
        
        $frontController->setBaseUrl($config['webroot']);
        
        $loader = new Zend_Loader_PluginLoader();
        
        $loader->addPrefixPath('Zend_View_Helper', EXTERNAL_LIBRARY_PATH . '/Zend/View/Helper/')
               ->addPrefixPath('Ml_View_Helper', APPLICATION_PATH . '/views/helpers');
        
        $classFileIncCache = CACHE_PATH . '/PluginDefaultLoaderCache.php';
        if (file_exists($classFileIncCache)) {
            require $classFileIncCache;
        }

        Zend_Loader_PluginLoader::setIncludeFileCache($classFileIncCache);

        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $viewRenderer->initView();

    }
}