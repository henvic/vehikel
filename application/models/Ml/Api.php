<?php

class Ml_Model_Api
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
    
    public function keyForm($consumer = false)
    {
        static $form = '';
        
        $registry = Zend_Registry::getInstance();
        
        if (! is_object($form)) {
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            if (! $consumer) {
                $action = $router->assemble(array(), "apply_api_key");
            } else {
                $action = $router->assemble(array("api_key" => $consumer['consumer_key']), "api_key");
            }
            
            $form = new Ml_Form_Api_Key(array(
                'action' => $action,
                'method' => 'post',
            ));
        }
        
        return $form;
    }
    
    public function deleteForm($consumer)
    {
        static $form = '';
        
        if (! is_object($form)) {
            
            $registry = Zend_Registry::getInstance();
            
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $form = new Ml_Form_Api_DeleteKey(array(
                'action' => $router->assemble(array("api_key" => $consumer['consumer_key']),
                "api_key_delete"),
                'method' => 'post',
            ));
        }
        
        return $form;
    }
    
    public function authorizeForm()
    {
        static $form = '';
        
        $registry = Zend_Registry::getInstance();
        
        if (! is_object($form)) {
            $form = new Ml_Form_Api_Authorize(array(
                'action' => '',
                'method' => 'post',
            ));
        }
        
        $form->setDefault("hash", $registry->get('globalHash'));
        
        return $form;
    }
}