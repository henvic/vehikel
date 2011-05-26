<?php

class Api extends Zend_Application_Resource_ResourceAbstract
{
	public function init()
	{
		Zend_Controller_Action_HelperBroker::addPath(APPLICATION_PATH .'/modules/'.HOST_MODULE.'/controllers/helpers');
		$loadoauthstore = Zend_Controller_Action_HelperBroker::getStaticHelper("LoadOauthstore");
		$loadoauthstore->setinstance();
        $loadoauthstore->preloadServer();
	}
}