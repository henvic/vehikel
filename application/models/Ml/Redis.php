<?php

class Ml_Model_Redis
{
    /**
     * Singleton instance
     *
     */
    protected static $_instance = null;
    
    
    /**
     * Singleton pattern implementation makes "new" unavailable
     *
     * @return void
     */
    protected function __construct()
    {
        Zend_Loader::loadFile("Predis.php", EXTERNAL_LIBRARY_PATH . "/predis/lib");
        
        $registry = Zend_Registry::getInstance();
        
        $config = $registry->get("config");
        
        $redisConfig = $config['cache']['backend']['redis'];
        $redis = new Predis\Client($redisConfig['servers']['global']);
        
        return $redis;
    }
    
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
            self::$_instance = new self();
        }

        return self::$_instance;
    }
}
