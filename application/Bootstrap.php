<?php

  function array_to_obj($array, &$obj)
  {
    foreach ($array as $key => $value)
    {
      if (is_array($value))
      {
      $obj->$key = new stdClass();
      array_to_obj($value, $obj->$key);
      }
      else
      {
        $obj->$key = $value;
      }
    }
  return $obj;
  }

function arrayToObject($array)
{
 $object= new stdClass();
 return array_to_obj($array,$object);
}

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initCache() //sysCache initialized in Start.php
	{
		$sysCache = Zend_Registry::getInstance()->get("sysCache");
		Zend_Date::setOptions(array('cache' => $sysCache));
		Zend_Locale::setCache($sysCache);
		Zend_Translate::setCache($sysCache);
	}
	
	protected function _initRun()
	{
		if(HOST_MODULE == 'default' || HOST_MODULE == 'api') $this->registerPluginResource("Uri");
		
		$config_array = $this->getOptions();
		//@todo don't use it anymore as a object to avoid this overhead
		Zend_Registry::getInstance()->set('config', arrayToObject($config_array));
		
		if(isset($config_array['email']['type']) && $config_array['email']['type'] == 'sendmail')
		{
    		Zend_Mail::setDefaultTransport(new Zend_Mail_Transport_Sendmail());
		} else {
    		Zend_Mail::setDefaultTransport(new Zend_Mail_Transport_Smtp($config_array['email']['smtp'], $config_array['email']));
		}
	}
	
	protected function _initAutoload()
    {
    	Zend_Loader_Autoloader::getInstance()->registerNamespace('ML_');
    }
	
	protected function _initDatabase()
	{
		try {
			$this->bootstrap('db');
    		
        	$db = $this->getResource('db');
        	
        	Zend_Db_Table_Abstract::setDefaultMetadataCache("sysCache");
	        
			Zend_Registry::getInstance()->set("database", $db);
		} catch(Exception $e)
		{
			echo "Error connecting to database.\n";
			throw $e;
		}
	}
	
    protected function _initRequest()
    {
    	require 'resources/Request'.HOST_MODULE.'NotPlugin.php';
    }
}
