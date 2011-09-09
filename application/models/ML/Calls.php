<?php

class ML_Calls extends ML_getModel
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
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    protected $_name = "calls";
    
    public static function form()
    {
        static $form = '';
        $registry = Zend_Registry::getInstance();
        if (! is_object($form)) {
            
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            require APPLICATION_PATH . '/forms/Call.php';
             
            $form = new Form_Call(array(
                'method' => 'post',
                'action' => $router->assemble(array(), "call")
            
            ));
        }
        
        $form->setDefault("hash", $registry->get('globalHash'));

        return $form;
    }
}