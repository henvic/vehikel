<?php

/**
 * @author Henrique Vicente <henriquevicente@gmail.com>
 * @license public domain
 */
class Ml_Model_CouchDb
{
    /**
     * Singleton instance
     */
    protected static $_instance = null;
    
    /**
     * Singleton pattern implementation makes "new" unavailable
     *
     * @return void
     */
    protected function __construct()
    {
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
            require EXTERNAL_LIBRARY_PATH."/php-on-couch/lib/couch.php";
            require EXTERNAL_LIBRARY_PATH."/php-on-couch/lib/couchClient.php";
            require EXTERNAL_LIBRARY_PATH."/php-on-couch/lib/couchDocument.php";
        
            $registry = Zend_Registry::getInstance();
            $config = $registry->get("config");
            
            $dsn = $config['resources']['db']['couchdb']['dsn'];
            
            $client = new couchClient($dsn, "web_access_log");
            
            self::$_instance = $client;
        }
        
        return self::$_instance;
    }
}
