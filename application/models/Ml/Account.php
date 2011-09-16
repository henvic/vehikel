<?php

class Ml_Model_Account
{
    public function settingsForm()
	/**
     * Singleton pattern implementation makes "new" unavailable
     *
     * @return void
     */
    //protected function __construct()
    //{
    //}

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
            
            $form = new Ml_Form_AccountSettings(array(
                'action' => $router->assemble(array(), "account"),
                'method' => 'post',
            ));
        }
        return $form;
    }
}