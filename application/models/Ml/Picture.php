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
    protected function __construct()
    {
    }
    
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
    
    public static function pictureForm()
    {
        static $form = '';
        
        if (! is_object($form)) {
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $form = new Ml_Form_Picture(array(
                'action' => $router->assemble(array(), "accountpicture"),
                'method' => 'post',
            ));
        }
        return $form;
    }
    
    public function deleteFiles($userInfo)
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        $s3config = $config['services']['S3'];
        
        $avatarInfo = unserialize($userInfo['avatarInfo']);
        if (!isset($avatarInfo['secret'])) {
            return false;
        }
        
        $s3 = new Zend_Service_Amazon_S3($s3config['key'], $s3config['secret']);
        
        foreach ($this->_sizes as $sizeInfo) {
            $s3->removeObject($s3config['headshotsBucket'] . "/" .
            $userInfo['id'] . '-' . $avatarInfo['secret'] . $sizeInfo[1] . '.jpg');
        }
        
        return true;
    }
    
    public function deleteAvatar($userInfo)
    {
        $this->deleteFiles($userInfo);
        
        $people = Ml_Model_People::getInstance();
        
        $people->update($userInfo['id'], array("avatarInfo" => serialize(array())));
        
        return true;
    }
    
    public function setAvatar($userInfo, $source)
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        $people = Ml_Model_People::getInstance();
        
        $s3config = $config['services']['S3'];
        
        /**
         * @todo rebuild this portion of code using another library
         * instead of letting it go to production or public distribution
         */
        require LIBRARY_PATH . '/phMagick/MODIFIEDphMagick.php';

        $image = new phMagick($source);
        
        $s3 = new Zend_Service_Amazon_S3($s3config['key'], $s3config['secret']);
        
        $image->setImageQuality(70);
        
        $dim = $image->getDimentions();
        
        if (! $dim) {
            return false;
        }
        
        list ($width, $height) = $dim;
        $unsharp = "-unsharp 0x0.4";
        $sizesInfo = array();
        $tmpname = array();
        foreach ($this->_sizes as $sizeInfo) {
            $tmpname[$sizeInfo[1]] = tempnam(sys_get_temp_dir(), 'HEADSHOT');
            
            $image->setSource($source);
            $image->setDestination($tmpname[$sizeInfo[1]]);
            
            if ($sizeInfo[0] == "sq") {
                $size = ($height < $width) ? $height : $width;
                //@todo let the user crop using Javascript, so he/she can set the offsets (default 0,0)
                $image->crop($size, $size, 0, 0, "center");
                $image->resize($sizeInfo[3], $sizeInfo[3], $unsharp);
            } else if ($width < $sizeInfo[3] &&
                $height < $sizeInfo[3] && $sizeInfo[2] != 'huge') {
                    copy($image->getSource(), $image->getDestination());
            } else {
                if ($width > $height) {
                    $image->resize($sizeInfo[3], 0, $unsharp);
                } else {
                    $image->resize(0, $sizeInfo[3], $unsharp);
                }
            }
            
            list ($widthThis, $heightThis) = $image->getDimentions();
            $sizesInfo[$sizeInfo[0]] = array("w" => $widthThis, "h" => $heightThis);
        }
        
        $oldData = unserialize($userInfo['avatarInfo']);
        
        //get the max value of mt_getrandmax() or the max value of the unsigned int type
        $maxRand = (mt_getrandmax() < 4294967295) ? mt_getrandmax() : 4294967295;
        $newSecret = mt_rand(0, $maxRand);//unsigned int
        if (isset($oldData['secret'])) {
            while ($oldData['secret'] == $newSecret) {
                $newSecret = mt_rand(0, $maxRand);
            }
        }
        
        foreach ($tmpname as $size => $file) {
            if ($size == '_h') {
                $privacy = Zend_Service_Amazon_S3::S3_ACL_PRIVATE;
            } else {
                $privacy = Zend_Service_Amazon_S3::S3_ACL_PUBLIC_READ;
            }
            
            $picAddr = $s3config['headshotsBucket'] . "/" . $userInfo['id'] . '-' . $newSecret . $size . '.jpg';
            
            $meta = array(Zend_Service_Amazon_S3::S3_ACL_HEADER =>
              $privacy,
              "Cache-Control" => "max-age=37580000, public",
              "Expires" => "Thu, 10 May 2029 00:00:00 GMT"
            );
            
            $s3->putFile($file, $picAddr, $meta);
            
            @unlink($file);
        }
        
        $newAvatarInfo = serialize(array("sizes" => $sizesInfo, "secret" => $newSecret));
        $people->update($userInfo['id'], array("avatarInfo" => $newAvatarInfo));
        
        //delete the old files
        $this->deleteFiles($userInfo);
        
        return true;
    }
}