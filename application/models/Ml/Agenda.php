<?php

class Ml_Model_Agenda extends Ml_Model_AccessSingleton
{
    protected static $_dbTableName = "agenda";
    
    /**
     * Singleton instance
     *
     */
    protected static $_instance = null;
    
    public static function form()
    {
        static $form = '';
        
        $registry = Zend_Registry::getInstance();
        
        if (! is_object($form)) {
            $form = new Ml_Form_Agenda(array(
                'method' => 'post',
            ));
        }
        
        $form->setDefault("hash", $registry->get('globalHash'));
        
        return $form;
    }
}