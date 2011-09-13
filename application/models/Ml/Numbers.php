<?php
class Ml_Numbers
{
    static public function base58Encode($num)
    {
        return self::baseEncode($num, 
        "123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ");
    }
    
    static public function base58Decode ($num)
    {
        return self::baseDecode($num, 
        "123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ");
    }
    
    static public function baseEncode($num, $alphabet)
    {
        $baseCount = strlen($alphabet);
        $encoded = '';
        while ($num >= $baseCount) {
            $div = $num / $baseCount;
            $mod = ($num - ($baseCount * intval($div)));
            $encoded = $alphabet[$mod] . $encoded;
            $num = intval($div);
        }
        if ($num) {
            $encoded = $alphabet[$num] . $encoded;
        }
        return $encoded;
    }
    
    static public function baseDecode($num, $alphabet)
    {
        $decoded = 0;
        $multi = 1;
        while (strlen($num) > 0) {
            $digit = $num[strlen($num) - 1];
            $decoded += $multi * strpos($alphabet, $digit);
            $multi = $multi * strlen($alphabet);
            $num = substr($num, 0, - 1);
        }
        return $decoded;
    }
    
    static public function isNaturalDbId($val)
    {
        if ((! Ml_Numbers::isNatural($val)) || (strval((int) ($val)) != (string) ($val))) {
            return false;
        }
        return true;
    }
    
    static public function isNatural($val, $acceptzero = false)
    {
        $return = ((string) $val === (string) (int) $val);
        if ($acceptzero) {
            $base = 0;
        } else {
            $base = 1;
        }
        if ($return && intval($val) < $base) {
            $return = false;
        }
        return $return;
    }
}