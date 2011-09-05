<?php

class ML_Picture
{
    /**
     * Singleton instance
     *
     * @var Zend_Auth
     */
    protected static $_instance = null;
    
    
    /**
     * Singleton pattern implementation makes "new" unavailable
     *
     * @return void
     */
    //protected function __construct()
    //{}
    //changing new ML_<model> to the getInstance method

    /**
     * Singleton pattern implementation makes "clone" unavailable
     *
     * @return void
     */
    protected function __clone()
    {}
    
    
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    /**
     * Usage
     * @param array with key => value, where key is the type of information and value is what's looked or just what's looked
     * @return array with a given's size datatable info and false in failure
     */
    public function getSizeInfo($sizeNeedle)
    {
        $match = false;
        $hasKey = false;
        
        if(is_array($sizeNeedle))
        {
            $hasKey = true;
            
            $key = array_search(key($sizeNeedle), $this->sizeTypes, true);
            
            $sizeNeedle = current($sizeNeedle);
        }
        
        foreach($this->Sizes as $size)
        {
            if(in_array($sizeNeedle, $size))
            {
                if($hasKey)
                {
                    if($size[$key] != $sizeNeedle) continue;
                }
                
                $match = array_combine($this->sizeTypes, $size);
            }
        }
        
        return $match;
    }
    
    /**
     * Calls the function above for each size and return
     * for every element $Sizes the appropriate data
     * @return array with information for each size
     */
    public function getSizesInfo()
    {
        $data = Array();
        foreach($this->Sizes as $size)
        {
            $data[] = $this->getSizeInfo(Array("name" => $size[2]));
        }
        
        return $data;
    }
    // There is a copy of this ($Sizes and $sizeTypes) in the Avatar helper
    /** Explantion for the $Sizes:
     * [0] => urihelper: for the links, i.e., /pictures/<id>/s is for the small pic (unused)
     * [1] => typeextension: for the picture uri, i.e., <id>/sq.jpg
     * [2] => name: the name of the resource
     * [3] => dimension: the largest possible dimension for that picture resource
     */
    protected $Sizes = Array(
        //Array("h", "-h", "huge", 2048),
        Array("b", "-b", "large", 1024),
        Array("m", "", "medium", 500),
        Array("s", "-m", "small", 240),
        Array("t", "-t", "thumbnail", 100),
        Array("sq", "-s", "square", 48),
    );
    
    // the types below are given as in the $Sizes array order
    protected $sizeTypes = Array("urihelper", "typeextension", "name", "dimension");
    
}