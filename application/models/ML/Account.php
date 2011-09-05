<?php

class ML_Account
{
    public function _getSettingsForm()
    {
        static $form = '';
        
        if(!is_object($form))
        {
            require_once APPLICATION_PATH . '/forms/AccountSettings.php';
            
            $form = new Form_AccountSettings(array(
                'action' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), "account"),
                'method' => 'post',
            ));
        }
        return $form;
    }
}