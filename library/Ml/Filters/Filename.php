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


class Ml_Filter_Filename implements Zend_Filter_Interface
{
    public function filter($value)
    {
        $value = mb_ereg_replace(' +', ' ', trim($value));
        $value = mb_ereg_replace("[\r\t\n]", "", $value);
        
        // http://www.asciitable.com/
        $value = trim($value, "\x22\x27\x26\x2C");
        
        // svn.wikimedia.org/viewvc/mediawiki/trunk/phase3/includes/normal/
        require_once EXTERNAL_LIBRARY_PATH . '/normal/UtfNormal.php';
        $value = UtfNormal::cleanUp($value);
        $value = mb_strtolower($value, "UTF-8");
        
        $bye = Array(
        ' ', '\"', '\'', '!', '@', '$', '%', '&', '*', '(', ')',
        ':', '=', '\'', '/', ';', '`', '<', '>', '[', ']',
        '?', '\\', ',', '#', 
        );
        
        $value = str_replace($bye, '', $value);
        
        $value = trim($value, ".");
        
        return $value;
    }
}
