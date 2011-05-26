<?php
require_once 'Zend/Validate/Abstract.php';
require_once 'Zend/Validate/Hostname.php';

class MLValidator_Url extends Zend_Validate_Abstract
{
    const INVALID_URL = 'invalidUrl';

    protected $_messageTemplates = array(
        self::INVALID_URL   => "The given URL is not valid",
    );

    public function isValid($value)
    {
        $valueString = (string) $value;
        $this->_setValue($valueString);
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
