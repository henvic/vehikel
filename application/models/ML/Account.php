<?php

class Ml_Account
{
    public function _getSettingsForm()
    {
        static $form = '';
        
        if (! is_object($form)) {
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            require APPLICATION_PATH . '/forms/AccountSettings.php';
            
            $form = new Form_AccountSettings(array(
                'action' => $router->assemble(array(), "account"),
                'method' => 'post',
            ));
        }
        return $form;
    }
}