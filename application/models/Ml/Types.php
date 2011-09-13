<?php
class Ml_Types
{
    function arrayToObject ($array, &$obj = false)
    {
        if (! $obj) {
            $obj = new stdClass();
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $obj->$key = new stdClass();
                array_to_obj($value, $obj->$key);
            } else {
                $obj->$key = $value;
            }
        }
        return $obj;
    }
}