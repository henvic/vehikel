<?php
class Ml_Model_Types
{
    public static function arrayToObject ($array, &$obj = false)
    {
        if (! $obj) {
            $obj = new stdClass();
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $obj->$key = new stdClass();
                self::arrayToObject($value, $obj->$key);
            } else {
                $obj->$key = $value;
            }
        }
        return $obj;
    }
}