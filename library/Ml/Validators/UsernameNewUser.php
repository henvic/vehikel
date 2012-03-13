<?php
//require_once 'Zend/Validate/Abstract.php';

class Ml_Validator_UsernameNewUser extends Zend_Validate_Abstract
{
    const MSG_USERNAME_RESERVED = 'usernameReserved';
    const MSG_USERNAME_INVALID = 'usernameInvalid';
    const MSG_USERNAME_EXISTS = 'usernameAlreadyExists';
    
    protected $_messageTemplates = array(
        self::MSG_USERNAME_RESERVED =>
        "This username is reserved and can not be registered",
        self::MSG_USERNAME_INVALID =>
        "This username is invalid. You can only use a-z, 0-9, _ and - for your username",
        self::MSG_USERNAME_EXISTS => "This username is already in use",
    );
 
    public function isValid($value)
    {
        $this->_setValue($value);
         
        $valueString = (string) $value;
        
        if (preg_match('#([^a-z0-9_-]+)#is', $value) || $value == '0') {
            $this->_error(self::MSG_USERNAME_INVALID);
            return false;
        }
        
        $reservedUsernames = require APPLICATION_PATH . "/configs/ReservedUsernames.php";
        
        if (in_array($value, $reservedUsernames)) {
            $this->_error(self::MSG_USERNAME_RESERVED);
            return false;    
        }
        
        if (mb_strlen($value) < 1 || mb_strlen($value) > 15) {
            return false;
        }
        
        $people = Ml_Model_People::getInstance();
        $getUserByUsername = $people->getByUsername($value);
        
        if (!empty($getUserByUsername)) {
            $this->_error(self::MSG_USERNAME_EXISTS);
            return false;
        }
        return true;
    }
}