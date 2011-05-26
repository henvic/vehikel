<?php
class ML_Recover extends ML_getModel
{
/**
     * Singleton instance
     *
     * @var Zend_Auth
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
	
	protected $_name = "recover";
	
	public function _getRecoverForm()
    {
        static $form = '';
        
        if(!is_object($form))
        {
        	require_once APPLICATION_PATH . '/forms/Recover.php';
        	
            $form = new Form_Recover(array(
                'action' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), "recover"),
                'method' => 'post',
            ));
        }
        return $form;
    }
}
