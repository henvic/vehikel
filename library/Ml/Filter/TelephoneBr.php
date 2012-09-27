<?php
class Ml_Filter_TelephoneBr implements Zend_Filter_Interface
{
    public function filter($value)
    {
        if (! $value) {
            return;
        }

        preg_match_all('!\d+!', $value, $matches);

        $implodedNumbers = implode('', $matches[0]);

        $value = $implodedNumbers;

        $sizeOfImplodedNumbers = mb_strlen($implodedNumbers);

        if (ctype_digit($implodedNumbers) && ($sizeOfImplodedNumbers == 10 | $sizeOfImplodedNumbers == 11)) {
            $value = mb_substr($implodedNumbers, 0, 2) .
                " " .
                mb_substr($implodedNumbers, 2, 4) .
                "-" .
                mb_substr($implodedNumbers, 6)
            ;
        }

        return $value;
    }
}
