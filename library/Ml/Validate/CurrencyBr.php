<?php

class Ml_Validate_CurrencyBr extends Zend_Validate_Abstract
{
    const INVALID = 'valueInvalid';
    const INVALID_FORMAT = 'invalidFormat';

    protected $_messageTemplates = array(
        self::INVALID => "Invalid type given. String expected",
        self::INVALID_FORMAT => "Formato não reconhecido, use o padrão: xx,xx. Exemplo: 15,20",
    );

    public function isValid($value)
    {
        if (! is_string($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        $this->_setValue($value);

        $posLastComma = strrpos($value, ",");

        $intPart = str_replace(".", "", mb_substr($value, 0, $posLastComma));

        $centsPart = mb_substr($value, $posLastComma + 1);

        if (! ctype_digit($intPart) || ! ctype_digit($centsPart) || mb_strlen($centsPart) != 2) {
            $this->_error(self::INVALID_FORMAT);
            return false;
        }

        return true;
    }
}
