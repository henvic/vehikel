<?php

class Ml_Api
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
            
            require APPLICATION_PATH . '/forms/api/apiKey.php';
            
            if (! $consumer) {
                $action = $router->assemble(array(), "apply_api_key");
            } else {
                $action = $router->assemble(array("api_key" => $consumer['consumer_key']), "api_key");
            }
            
            $form = new Form_APIkey(array(
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
            
            require APPLICATION_PATH . '/forms/api/apiKeyDelete.php';
            
            $form = new Form_DeleteApiKey(array(
                'action' => $router->assemble(array("api_key" => $consumer['consumer_key']), "api_key_delete"),
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
            require APPLICATION_PATH . '/forms/api/authorize.php';
            
            $form = new Form_authorize(array(
                'action' => '',
                'method' => 'post',
            ));
        }
        
        $form->setDefault("hash", $registry->get('globalHash'));
        
        return $form;
    }
}