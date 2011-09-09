<?php

class ML_Agenda extends ML_getModel
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
    
    protected $_name = "agenda";
    
    public static function form()
    {
        static $form = '';
        
        $registry = Zend_Registry::getInstance();
        
        if (! is_object($form)) {
            require APPLICATION_PATH . '/forms/Agenda.php';
             
            $form = new Form_Agenda(array(
                'method' => 'post',
            ));
        }
        
        $form->setDefault("hash", $registry->get('globalHash'));

        return $form;
    }
}