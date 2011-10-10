<?php

class Ml_Model_Logger
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
    
    
    /**
     * 
     * Log important actions
     * @param array $data with at least action key
     * @param bool logPost whether to log POST data
     */
    public function log(array $data, $logPost = false)
    {
        $auth = Zend_Auth::getInstance();
        
        $couchDb = Ml_Model_CouchDb::getInstance();
        
        if (! isset($data['action'])) {
            throw new Exception("Action key doesn't exists in the data array.");
        }
        
        if (isset($data['server']) || isset($data['raw_post']) ||
        isset($data['uid']) || isset($data['_id']) || isset($data['_rev'])) {
            throw new Exception("Trying to use reserved log internal key.");
        }
        
        $data['server'] = filter_input_array(INPUT_SERVER, FILTER_UNSAFE_RAW);
        
        if ($logPost) {
            $data['raw_post'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);
        }
        
        $data['uid'] = $auth->getIdentity();
        
        $data['_id'] = Ml_Model_Request::getId();
        
        $dataObject = Ml_Model_Types::arrayToObject($data);
        
        $couchDb->useDatabase("actions_log");
        $couchDb->storeDoc($dataObject);
    }
}