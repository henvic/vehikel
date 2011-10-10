<?php
/**
 * 
 * Generates a unique request ID
 * @uses Zend_Auth
 *
 */
class Ml_Model_Request
{
    protected static $_id;
    
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
    
    public static function getId()
    {
        if (null === self::$_id) {
            $auth = Zend_Auth::getInstance();
            
            $uid = $auth->getIdentity();
            
            $uid = $auth->getIdentity();
            
            if ($uid) {
                self::$_id = $_SERVER['REQUEST_TIME'] . '-' . $_SERVER['REMOTE_ADDR'] . '-' .
                $auth->getIdentity() . '-' . mt_rand();
            } else {
                self::$_id = $_SERVER['REQUEST_TIME'] . '-' . $_SERVER['REMOTE_ADDR'] .
                '-guest-' . mt_rand() . '-' . mt_rand();
            }
        }
        
        return self::$_id;
    }
}