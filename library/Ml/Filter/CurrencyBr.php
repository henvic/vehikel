<?php

class Ml_Filter_CurrencyBr implements Zend_Filter_Interface
{
    public function filter($value)
    {
        if (! $value) {
            return $value;
        }

        $currency = new Zend_Currency(array("symbol" => "R$&nbsp;"), "pt_BR");

        $posLastComma = strrpos($value, ",");

        if (! $posLastComma) {
            $intPart = str_replace(array(",", "."), "", $value);
            if (! ctype_digit($intPart)) {
                return $value;
            } else {
                $filteredValue = $currency->setValue($intPart);
                return $filteredValue->toCurrency(null, array("display" => Zend_Currency::NO_SYMBOL));
            }
        }

        $intPart = str_replace(array(",", "."), "", mb_substr($value, 0, $posLastComma));

        $centsPart = mb_substr(str_replace(array(",", "."), "", mb_substr($value, $posLastComma)), 0);

        if (! ctype_digit($intPart) || ! (ctype_digit($centsPart) || empty($centsPart))) {
            return $value;
        }

        $centsSize = mb_strlen($centsPart);

        if ($centsSize == 1) {
            $filteredValue = $currency->setValue(($intPart . $centsPart . "0")  / 100);
            return $filteredValue->toCurrency(null, array("display" => Zend_Currency::NO_SYMBOL));
        } else if ($centsSize == 2) {
            $filteredValue = $currency->setValue(($intPart . $centsPart) / 100);
            return $filteredValue->toCurrency(null, array("display" => Zend_Currency::NO_SYMBOL));
        } else if (! $centsSize) {
            $filteredValue = $currency->setValue($intPart);
            return $filteredValue->toCurrency(null, array("display" => Zend_Currency::NO_SYMBOL));
        } else {
            return $value;
        }
    }
}
