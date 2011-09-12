<?php
require EXTERNAL_LIBRARY_PATH . "/predis/lib/Predis.php";

class Ml_Model_Redis
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
    {
    }
    
    
    public static function getInstance()
    {
        if (null === self::$_instance) {
            $registry = Zend_Registry::getInstance();
            $config = $registry->get("config");
            
            $redisConfig = $config['cache']['backend']['redis'];
            
            $redis = new Predis\Client($redisConfig['servers']['global']);
            self::$_instance = $redis;
        }

        return self::$_instance;
    }
}
