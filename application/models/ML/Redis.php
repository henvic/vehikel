<?php
require EXTERNAL_LIBRARY_PATH . "/predis/lib/Predis.php";

class ML_Redis
{
/**
     * Singleton instance
     *
     * @var Zend_Auth
     */
    protected static $_instance = null;
	
	
    /**
     * Singleton pattern implementation makes "new" unavailable
     *
     * @return void
     */
    //protected function __construct()
    //{}

    /**
     * Singleton pattern implementation makes "clone" unavailable
     *
     * @return void
     */
    protected function __clone()
    {}
    
    
    public static function getInstance()
    {
        if (null === self::$_instance) {
        	$registry = Zend_Registry::getInstance();
        	
        	//@todo after migrating the $config to array-style make this configuration more practical
			$redis = new Predis\Client(array("password" => $registry->get("config")->cache->backend->redis->servers->global->password));
			self::$_instance = $redis;
        }

        return self::$_instance;
    }
}
