<?php

class Ml_Model_Account
{
	/**
     * Makes "new" unavailable
     *
     * @return void
     */
    protected function __construct()
    {
    }

    /**
     * Makes "clone" unavailable
     *
     * @return void
     */
    protected function __clone()
    {
    }
    
    public static function settingsForm()
    {
        static $form = '';
        
        if (! is_object($form)) {
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $form = new Ml_Form_AccountSettings(array(
                'action' => $router->assemble(array(), "account"),
                'method' => 'post',
            ));
        }
        return $form;
    }
}