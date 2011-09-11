<?php
/*
 * Checks if the protocol being used is HTTPS or not
 * 
 * This is useful to avoid users sending data over HTTP
 * 
 * Useful to avoid credential information to be sent insecurely
 * 
 * It's more useful at the development stage to avoid mistakingly
 * sending the form action over HTTP, but of course has its importance
 * at the production server as a safe-guard and because you can never
 * trust that the user's behaviour will be the intended one
 * 
 * i.e., imagine if for some reason the user makes a script that tries
 * to log in the web service over HTTP without the proper API methods
 * or something like that
 * 
 */
//require_once 'Zend/Validate/Abstract.php';


class Ml_Validator_Https extends Zend_Validate_Abstract
{
    const MSG_NOT_HTTPS = 'notHttps';
 
    protected $_messageTemplates = array(
        self::MSG_NOT_HTTPS =>
         "This form is supposed to be sent over the HTTPS protocol only.",
    );
 
    public function isValid($value)
    {
        if (! isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on") {
            $this->_error(self::MSG_NOT_HTTPS);
            return false;
        }
        return true;
    }
}
