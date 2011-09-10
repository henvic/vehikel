<?php

class Ml_Coupons extends Ml_Db
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
    
    protected $_name = "coupons";
    
    public function _RedeemForm()
    {
        static $form = '';
        
        if (! is_object($form)) {
            
            $router = Zend_Controller_Front::getInstance()->getRouter();    
            require APPLICATION_PATH . '/forms/Redeem.php';
            
            $form = new Form_Redeem(array(
                'action' => $router->assemble(array(), "do_order"),
                'method' => 'post',
            ));
        }
        return $form;
    }
}