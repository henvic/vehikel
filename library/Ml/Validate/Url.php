<?php
//require_once 'Zend/Validate/Abstract.php';
//require_once 'Zend/Validate/Hostname.php';

class Ml_Validate_Url extends Zend_Validate_Abstract
{
    const INVALID = 'valueInvalid';
    const INVALID_URL = 'invalidUrl';

    protected $_messageTemplates = array(
        self::INVALID => "Invalid type given. String expected",
        self::INVALID_URL   => "The given URL is not valid",
    );

    public function isValid($value)
    {
        $this->_setValue($value);

        if (! is_string($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        Zend_Uri::setConfig(array('allow_unwise' => true));
        $check = Zend_Uri::check($value);
        Zend_Uri::setConfig(array('allow_unwise' => false));
        if (!$check) {
            $this->_error(self::INVALID_URL);
            return false;
        }
        return true;
    }
}
