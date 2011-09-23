<?php

class Ml_Model_Calls extends Ml_Model_AccessSingleton
{
    protected static $_dbTableName = "calls";
    
    /**
     * Singleton instance
     *
     */
    protected static $_instance = null;
    
    public static function form()
    {
        $registry = Zend_Registry::getInstance();
        
        static $form = '';
        
        if (! is_object($form)) {
            
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $form = new Ml_Form_Call(array(
                'method' => 'post',
                'action' => $router->assemble(array(), "call")
            
            ));
        }
        
        $form->setDefault("hash", $registry->get('globalHash'));

        return $form;
    }
}