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
        	$config = $registry->get("config");
        	
			$redis = new Predis\Client($config['cache']['backend']['redis']['servers']['global']);
			self::$_instance = $redis;
        }

        return self::$_instance;
    }
}
