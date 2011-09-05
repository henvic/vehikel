<?php

class ML_Coupons extends ML_getModel
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
    {}
    
    
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
        
        if(!is_object($form))
        {
            require APPLICATION_PATH . '/forms/Redeem.php';
            
            $form = new Form_Redeem(array(
                'action' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), "do_order"),
                'method' => 'post',
            ));
        }
        return $form;
    }
}