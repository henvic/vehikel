<?php
/*
 * There are two similar filters
 * 
 * The FilenameRobot has (filename fetched)
 * $end = mb_strlen($value);
        if($end > 60)
        {
            $value = mb_substr($value, $end - 60);
        }
 * 
 * while the Filename doesn't (web interface, user typed).
 */


class Ml_Filter_FilenameRobot implements Zend_Filter_Interface
{
    public function filter($value)
    {
        $value = mb_ereg_replace(' +', ' ', trim($value));
        $value = mb_ereg_replace("[\r\t\n]", "", $value);
        
        // http://www.asciitable.com/
        $value = trim($value, "\x22\x27\x26\x2C");
        
        $value = preg_replace('/\p{M}/u', '', Normalizer::normalize($value, Normalizer::FORM_D));
        $value = mb_strtolower($value, "UTF-8");
        
        $bye = Array(
        ' ', '\"', '\'', '!', '@', '$', '%', '&', '*', '(', ')',
        ':', '=', '\'', '/', ';', '`', '<', '>', '[', ']',
        '?', '\\', ',', '#', 
        );
        
        $value = str_replace($bye, '', $value);
        
        $end = mb_strlen($value);
        if ($end > 60) {
            $value = mb_substr($value, $end - 60);
        }
        
        $value = trim($value, ".");
        
        return $value;
    }
}
