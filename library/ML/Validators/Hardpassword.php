<?php
//require_once 'Zend/Validate/Abstract.php';

class MLValidator_Hardpassword extends Zend_Validate_Abstract
{
    const MSG_PASSWORD_NOT_HARD = 'passwordNotHard';
    
    protected $_messageTemplates = array(
        self::MSG_PASSWORD_NOT_HARD => "Your password can not be this easy",
    );
 
    public function isValid($value, $context = null)
    {
        $this->_setValue($value);
         
        $valueString = (string) $value;
        
        if (isset($context['currentpassword'])) {
            //this is handled by NewPassword.php
            unset($context['currentpassword']);
        }
        
        if (isset($context['password_confirm'])) {
            unset($context['password_confirm']);
        }
        
        unset($context['password']);
        
        if (in_array($value, $context)) {
            $this->_error(self::MSG_PASSWORD_NOT_HARD);
            return false;
        }
        
        return true;
    }
}