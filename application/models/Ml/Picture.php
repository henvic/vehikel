<?php

class Ml_Model_Picture
{
    /** Explantion for the $sizes array data:
     * 0: urihelper: for the links, i.e., /pictures/<id>/s is for the small pic
     * 1: typeextension: for the picture uri, i.e., <id>/sq.jpg
     * 2: name: the name of the resource
     * 3: dimension: the largest possible dimension for that picture resource
     */
    protected $_sizes = array(//array("h", "-h", "huge", 2048),
        array("b", "-b", "large", 1024),
        array("m", "", "medium", 500),
        array("s", "-m", "small", 240),
        array("t", "-t", "thumbnail", 100),
        array("sq", "-s", "square", 48),
    );
    
    // the types below are given as in the $sizes array order
    protected $_sizeTypes =
     array("urihelper", "typeextension", "name", "dimension");
    
    /**
     * Singleton instance
     *
     */
    protected static $_instance = null;
    
    
    /**
     * Singleton pattern implementation makes "new" unavailable
     *
     * @return void
     */
    //protected function __construct()
    //{
    //}
    
    /**
     * Singleton pattern implementation makes "clone" unavailable
     *
     * @return void
     */
    protected function __clone()
    {
    }
    
    
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    /**
     * Get image size infos
     * @param array with key => value, where key is the type of information
     * @return array with a given's size datatable info and false in failure
     */
    public function getSizeInfo($sizeNeedle)
    {
        $match = false;
        $hasKey = false;
        
        if (is_array($sizeNeedle)) {
            $hasKey = true;
            
            $key = array_search(key($sizeNeedle), $this->_sizeTypes, true);
            
            $sizeNeedle = current($sizeNeedle);
        }
        
        foreach ($this->_sizes as $size) {
            if (in_array($sizeNeedle, $size)) {
                if ($hasKey) {
                    if ($size[$key] != $sizeNeedle) {
                        continue;
                    }
                }
                
                $match = array_combine($this->_sizeTypes, $size);
            }
        }
        
        return $match;
    }
    
    /**
     * Calls the function above for each size and return
     * for every element $sizes the appropriate data
     * @return array with information for each size
     */
    public function getSizesInfo()
    {
        $data = array();
        foreach ($this->_sizes as $size) {
            $data[] = $this->getSizeInfo(array("name" => $size[2]));
        }
        
        return $data;
    }
}