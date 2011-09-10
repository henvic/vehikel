<?php
//require_once 'Zend/Validate/Abstract.php';

class MLValidator_Username extends Zend_Validate_Abstract
{
    const MSG_USERNAME_NOT_FOUND = 'usernameNotFound';
    const MSG_EMAIL_NOT_FOUND = 'emailNotFound';
    
    protected $_messageTemplates = array(
        self::MSG_USERNAME_NOT_FOUND => "User not found",
        self::MSG_EMAIL_NOT_FOUND => "Email not found",
);
 
    public function isValid($value)
    {
        $this->_setValue($value);
         
        $valueString = (string) $value;
        
        $people = new Ml_People();
        
        if (mb_strstr($value, "@")) {
            $getUserByEmail = $people->getByEmail($value);
            
            if (empty($getUserByEmail)) {
                $this->_error(self::MSG_EMAIL_NOT_FOUND);
                return false;
            }
            
            Zend_Registry::getInstance()->set("loginUserInfo", $getUserByEmail);
            
            return true;
        }
        
        if (mb_strlen($value) == 0) {
            return false;
        }
        
        if (mb_strlen($value) > 20) {
            $this->_error(self::MSG_USERNAME_NOT_FOUND);
            return false;
        }
        
        if (preg_match('#([^a-z0-9_-]+)#is', $value) || $value == '0') {
            $this->_error(self::MSG_USERNAME_NOT_FOUND);
            return false;
        }
        
        $getUserByUsername = $people->getByUsername($value);
        
        if (empty($getUserByUsername)) {
           $this->_error(self::MSG_USERNAME_NOT_FOUND);
           return false;
        }
        
        Zend_Registry::getInstance()->set("loginUserInfo", $getUserByUsername);
        
        return true;
    }
}