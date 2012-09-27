<?php

class Ml_Validate_Cep extends Zend_Validate_Abstract
{
    const INVALID = 'valueInvalid';
    const INVALID_CEP = 'cepInvalid';

    protected $_messageTemplates = array(
        self::INVALID => "Invalid type given. String expected",
        self::INVALID_CEP   => "CEP inválido",
    );

    public function isValid($value)
    {
        $this->_setValue($value);

        if (! is_string($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        if (mb_strlen($value) == 9 &&
            ctype_digit(mb_substr($value, 0, 5)) &&
            mb_substr($value, 5, 1) == "-" &&
            ctype_digit(mb_substr($value, 6, 3))) {
            return true;
        }

        $this->_error(self::INVALID_CEP);
        return false;
    }
}
