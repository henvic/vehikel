<?php
require_once 'Zend/Validate/Abstract.php';
require_once 'Zend/Validate/Hostname.php';


class MLValidator_AccountRecover extends Zend_Validate_Abstract
{
    const MSG_USERNAME_NOT_FOUND = 'usernameNotFound';
    const MSG_EMAIL_NOT_FOUND = 'emailNotFound';
    
    protected $_messageTemplates = array(
        self::MSG_USERNAME_NOT_FOUND => "No user by this username was found.",
        self::MSG_EMAIL_NOT_FOUND => "No user using this email was found.",
    );
 
    public function isValid($value)
    {
        $this->_setValue($value);
         
        $valueString = (string) $value;
        
        if(mb_strlen($value) < 1 || mb_strlen($value) > 100) return false;
        
        $method = (strpos($value, '@') === FALSE) ? "alias" : "email";
        
        $People = ML_People::getInstance();
        
        $getUser = ($method ==  "alias") ? $People->getByUsername($value) : $People->getByEmail($value); 
           if(empty($getUser)) {
               if($method == "alias") $this->_error(self::MSG_USERNAME_NOT_FOUND);
               else $this->_error(self::MSG_EMAIL_NOT_FOUND);
               return false;
           }
           
           Zend_Registry::getInstance()->set("accountRecover", $getUser);
           
        return true;
    }
}