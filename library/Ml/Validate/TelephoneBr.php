<?php

class Ml_Validate_TelephoneBr extends Zend_Validate_Abstract
{
    const INVALID = 'valueInvalid';
    const MISSING_AREA_CODE = 'missingAreaCode';

    protected $_messageTemplates = array(
        self::INVALID => "Invalid type given. String expected",
        self::MISSING_AREA_CODE => "Telefone em formato não reconhecido, use o padrão: DDD xxxx-xxxx(x)",
    );

    public function isValid($value)
    {
        $this->_setValue($value);

        if (! is_string($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        $unformattedValue = str_replace(array(" ", "-"), "", $value);

        $sizeOfNumber = mb_strlen($unformattedValue);

        if (ctype_digit($unformattedValue) && ($sizeOfNumber == 10 || $sizeOfNumber == 11)) {
            return true;
        }

        $this->_error(self::MISSING_AREA_CODE);
        return false;
    }
}
