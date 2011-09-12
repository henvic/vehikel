<?php

class Ml_Account
{
    public function settingsForm()
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