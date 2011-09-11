<?php
class Ml_Filter_UrlFilter implements Zend_Filter_Interface
{
    public function filter($value)
    {
        if (! $value || mb_substr($value, 0, 7) == 'http://' ||
         mb_substr($value, 0, 8) == 'https://') {
            return $value;
        }
        
        $value = 'http://'.$value;
        return $value;
    }
}
