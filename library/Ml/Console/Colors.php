<?php

/**
 * Coloring library for CLI (command-line interface) strings
 * 
 * Adapted from PHP CLI Colors – PHP Class Command Line Colors (bash)
 * from http://goo.gl/0R7lY
 *
 */
class Ml_Console_Colors
{
    private $_foregroundColors = array(
        'black' => '0;30',
        'dark_gray' => '1;30',
        'blue' => '0;34',
        'light_blue' => '1;34',
        'green' => '0;32',
        'light_green' => '1;32',
        'cyan' => '0;36',
        'light_cyan' => '1;36',
        'red' => '0;31',
        'light_red' => '1;31',
        'purple' => '0;35',
        'light_purple' => '1;35',
        'brown' => '0;33',
        'yellow' => '1;33',
        'light_gray' => '0;37',
        'white' => '1;37',
    );
    
    private $_backgroundColors = array(
        'black' => '40',
        'red' => '41',
        'green' => '42',
        'yellow' => '43',
        'blue' => '44',
        'magenta' => '45',
        'cyan' => '46',
        'light_gray' => '47',
    );
    
    // Returns colored string
    public function getColoredString($string, $foregroundColor = null, 
    $backgroundColor = null)
    {
        $coloredString = "";
        
        // Check if given foreground color found
        if (isset($this->_foregroundColors[$foregroundColor])) {
            $coloredString .= "\033[" .
             $this->_foregroundColors[$foregroundColor] . "m";
        }
        // Check if given background color found
        if (isset($this->_backgroundColors[$backgroundColor])) {
            $coloredString .= "\033[" .
             $this->_backgroundColors[$backgroundColor] . "m";
        }
        // Add string and end coloring
        $coloredString .= $string;
        
        if (isset($this->_foregroundColors[$foregroundColor]) ||
         isset($this->_backgroundColors[$backgroundColor])) {
             $coloredString .= "\033[0m";
        }
        
        return $coloredString;
    }
    
    /**
     * 
     * Returns all foreground color names
     * @return array of foreground color names
     */
    public function getForegroundColors()
    {
        return array_keys($this->_foregroundColors);
    }
    
    /**
     * 
     * Returns all background color names
     * @return array of background color names
     */
    public function getBackgroundColors()
    {
        return array_keys($this->_backgroundColors);
    }
}