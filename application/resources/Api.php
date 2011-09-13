<?php
class Ml_Resource_Api extends Zend_Application_Resource_ResourceAbstract
{
    public function init ()
    {
        Zend_Controller_Action_HelperBroker
        ::addPath(APPLICATION_PATH . '/modules/' .
        HOST_MODULE . '/controllers/helpers');
        $loadOauthStore = Zend_Controller_Action_HelperBroker
        ::getStaticHelper("LoadOauthstore");
        $loadOauthStore->setinstance();
        $loadOauthStore->preloadServer();
    }
}