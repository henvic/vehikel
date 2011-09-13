<?php
/**
 * 
 * Converts filesizes from bits or bytes to a human-readable format
 * Idea found at http://snipplr.com/view/4633/convert-size-in-kb-mb-gb-/
 *
 */
class Ml_View_Helper_FileSize extends Zend_View_Helper_Abstract
{
    protected $_units = array(" Bytes",
    " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
    
    /**
     * @param integer $size
     * @param bool $isBytes true for size given in bytes and false for in bits
     */
    function fileSize($size, $isBytes = false)
    {
        if (! $isBytes) {
            $size /= 8;
        }
        
        if ($size) {
            $human = round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) .
            $this->_units[$i];
        } else {
            $human = '0 Bytes';
        }
        
        return $human;
    }
}
