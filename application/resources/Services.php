<?php
class Ml_Resource_Services extends Zend_Application_Resource_ResourceAbstract
{
    public function init ()
    {
        Zend_Controller_Action_HelperBroker::addPath(APPLICATION_PATH . '/controllers/helpers');
        
        $frontController = $this->getBootstrap()->getResource('FrontController');
        
        $frontController
        ->setParam('noViewRenderer', true)
        ->addModuleDirectory(APPLICATION_PATH.'/modules')
        ->addControllerDirectory(APPLICATION_PATH . '/modules/' . HOST_MODULE . '/controllers')
        ->setRequest(new Zend_Controller_Request_Simple())
        ->setRouter(new Ml_Controller_Router_Cli())
        ->setResponse(new Zend_Controller_Response_Cli())
        ;
    }
}