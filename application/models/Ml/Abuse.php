<?php
class Ml_Model_Abuse extends Ml_Model_Db_Table
{
    protected $_name = "abuse";
    
	/**
     * Singleton pattern implementation makes "new" unavailable
     *
     * @return void
     */
    //protected function __construct()
    //{
    //}

    public function form()
    /**
     * Singleton pattern implementation makes "clone" unavailable
     *
     * @return void
     */
    protected function __clone()
    {
    }
    
    {
        static $form = '';
        
        if (! is_object($form)) {
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $form = new Ml_Form_Abuse(array(
                'action' => $router->assemble(array(), "report_abuse"),
                'method' => 'post',
            ));
        }
        
        return $form;
    }
}