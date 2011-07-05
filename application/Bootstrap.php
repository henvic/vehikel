<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initRun()
	{
		if(HOST_MODULE == 'default' || HOST_MODULE == 'api') $this->registerPluginResource("Uri");
		
		$config_array = $this->getOptions();
		
		Zend_Registry::getInstance()->set('config', new Zend_Config($config_array));
		
	if(isset($config_array['email']['type']) && $config_array['email']['type'] == 'sendmail')
		{
    		Zend_Mail::setDefaultTransport(new Zend_Mail_Transport_Sendmail());
		} else {
    		Zend_Mail::setDefaultTransport(new Zend_Mail_Transport_Smtp($config_array['email']['smtp'], $config_array['email']));
		}
	}
	
	protected function _initAutoload()
    {
    	$autoloader = Zend_Loader_Autoloader::getInstance();
    	$autoloader->registerNamespace('ML_');
    }
	
	protected function _initDatabase()
	{
		try {
			$this->bootstrap('db');
    		
        	$db = $this->getResource('db');
	        
			
			Zend_Registry::getInstance()->set("database", $db);
		} catch(Exception $e)
		{
			echo "Error connecting to database.\n";
			throw $e;
		}
	}
	
    protected function _initRequest()
    {
    	require_once 'resources/Request'.HOST_MODULE.'NotPlugin.php';
    }
}
