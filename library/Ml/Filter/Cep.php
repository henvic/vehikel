<?php
class Ml_Filter_Cep implements Zend_Filter_Interface
{
    public function filter($value)
    {
        if (! $value) {
            return;
        }

        preg_match_all('!\d+!', $value, $matches);

        if (isset($matches[0])) {
            $implodedNumbers = implode('', $matches[0]);

            if (ctype_digit($implodedNumbers) && mb_strlen($implodedNumbers) == 8) {
                $value = mb_substr($implodedNumbers, 0, 5) . "-" . mb_substr($implodedNumbers, 5, 3);
            }
        }

        return $value;
    }
}
