<?php

class ML_Verhoeff
{
    // put in this class by Henrique Vicente <henriquevicente@gmail.com>
    // calcsum returns the digit, checksum returns 0 if the number+digit is a match
    // verhoeff.php
    // Author: Anders Dahnielson
    // URL: http://en.dahnielson.com/2006/09/verhoeff.html
    // Description: Implementation of Verhoeff's Dihedral Group D5 Check
    // Copyright: No rights reserved. 
    
    protected static function d($j, $k)
    {
        $table = array(
            array(0,1,2,3,4,5,6,7,8,9),
            array(1,2,3,4,0,6,7,8,9,5),
            array(2,3,4,0,1,7,8,9,5,6),
            array(3,4,0,1,2,8,9,5,6,7),
            array(4,0,1,2,3,9,5,6,7,8),
            array(5,9,8,7,6,0,4,3,2,1),
            array(6,5,9,8,7,1,0,4,3,2),
            array(7,6,5,9,8,2,1,0,4,3),
            array(8,7,6,5,9,3,2,1,0,4),
            array(9,8,7,6,5,4,3,2,1,0),
            );
        
        return $table[$j][$k];
    }
    
    protected static function p($pos, $num)
    {
        $table = array(
            array(0,1,2,3,4,5,6,7,8,9),
            array(1,5,7,6,2,8,3,0,9,4),
            array(5,8,0,3,7,9,6,1,4,2),
            array(8,9,1,6,0,4,3,5,2,7),
            array(9,4,5,3,1,2,6,8,7,0),
            array(4,2,8,6,5,7,3,9,0,1),
            array(2,7,9,3,8,0,6,4,1,5),
            array(7,0,4,6,9,1,3,2,5,8),
            );
        
        return $table[$pos % 8][$num];
    }
    
    protected static function inv($j)
    {
        $table = array(0,4,3,2,1,5,6,7,8,9);
        return $table[$j];
    }
    
    public static function calcsum($number)
    {
        $c = 0;
        $n = strrev($number);
    
        $len = strlen($n);
        for ($i = 0; $i < $len; $i++)
            $c = self::d($c, self::p($i+1, $n[$i]));
    
        return self::inv($c);
    }
    
    public static function checksum($number)
    {
        $c = 0;
        $n = strrev($number);
    
        $len = strlen($n);
        for ($i = 0; $i < $len; $i++)
            $c = self::d($c, self::p($i, $n[$i]));
    
        return $c;
    }
}