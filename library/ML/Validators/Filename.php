<?php
//require_once 'Zend/Validate/Abstract.php';

class Ml_Validator_Filename extends Zend_Validate_Abstract
{
    const MSG_FILENAME_INVALID = 'filenameInvalid';
 
    protected $_messageTemplates = array(
        self::MSG_FILENAME_INVALID => "This filename is invalid",
    );
 
    public function isValid($value)
    {
        $this->_setValue($value);
 
        $valueString = (string) $value;
        
        if (ctype_punct($value)) {
            $this->_error(self::MSG_FILENAME_INVALID);
            return false;
        }
        return true;
    }
}