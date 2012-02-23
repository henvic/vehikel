<?php
class Ml_Resource_Api extends Zend_Application_Resource_ResourceAbstract
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
        
        $router->removeDefaultRoutes();
        
        $compat = new Zend_Controller_Router_Route_Module(array(), $dispatcher, $request);
        $router->addRoute(HOST_MODULE, $compat);
        
        $routerConfig = $cacheFiles->getConfigIni(APPLICATION_PATH . '/configs/' . HOST_MODULE . 'Routes.ini');
        
        $router->addConfig($routerConfig, "apiroutes");
        
        Zend_Controller_Action_HelperBroker::addPath(APPLICATION_PATH . '/modules/' . HOST_MODULE . '/controllers/helpers');
        
        $loadOauthStore = Zend_Controller_Action_HelperBroker::getStaticHelper("LoadOauthstore");
        
        $loadOauthStore->setinstance();
        $loadOauthStore->preloadServer();
        
        $frontController
        ->setBaseUrl($config['apiroot'])
        ->setParam('noViewRenderer', true)
        ->addModuleDirectory(APPLICATION_PATH . '/modules')
        ->addControllerDirectory(APPLICATION_PATH . '/modules/' . HOST_MODULE . '/controllers');
        
        $response = new Zend_Controller_Response_Http;
        
        if (filter_input(INPUT_GET, "responseformat", FILTER_UNSAFE_RAW) == 'json') {
            $contentType = 'application/json';
        } else {
            $contentType = 'text/xml';
        }
        
        $response->setHeader('Content-Type', $contentType . '; charset=utf-8', true);
        
        $frontController->setResponse($response);
    }
}