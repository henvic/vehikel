<?php
/**
 * @todo rebuild this portion of code using another library
 * instead of letting it go to production
 */

require_once LIBRARY_PATH . '/phMagick/MODIFIEDphMagick.php';

class Ml_Model_PictureUpload extends Ml_Model_Picture
{
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
        
        $avatarInfo = unserialize($userInfo['avatarInfo']);
        if (!isset($avatarInfo['secret'])) {
            return false;
        }
        
        $s3 = new Zend_Service_Amazon_S3($config['services']['S3']['key'], $config['services']['S3']['secret']);
        
        foreach ($this->sizes as $sizeInfo) {
            $s3->removeObject($config['services']['S3']['headshotsBucket']."/".$userInfo['id'].'-'.$avatarInfo['secret'].$sizeInfo[1].'.jpg');
        }
        
        return true;
    }
    
    public function deleteAvatar($userInfo)
    {
        $this->deleteFiles($userInfo);
        
        $people = Ml_Model_People::getInstance();
        
        $people->update(array("avatarInfo" => serialize(array())), $people->getAdapter()->quoteInto("id = ?", $userInfo['id']));
        
        return true;
    }
    
    public function setAvatar($userInfo, $originalfile)
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        $people = Ml_Model_People::getInstance();
        
        $image = new phMagick($originalfile);
        
        $s3 = new Zend_Service_Amazon_S3($config['services']['S3']['key'], $config['services']['S3']['secret']);
        
        $image->setImageQuality(70);
        
        $dim = $image->getDimentions();
        
        if (! $dim) {
            return false;
        }
        
        list ($width, $height) = $dim;
        $unsharp = "-unsharp 0x0.4";
        $sizesInfo = array();
        $tmpname = array();
        foreach ($this->sizes as $sizeInfo) {
            $tmpname[$sizeInfo[1]] = tempnam(sys_get_temp_dir(), 'HEADSHOT');
            
            $image->setSource($originalfile);
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
        
        //I know this is insane, but I'm an unperfect perfectionist
        //I think this is for a bank account ha!
        $maxRand = (mt_getrandmax() < 4294967295) ? mt_getrandmax() : 4294967295;
        $newSecret = mt_rand(0, $maxRand);//unsigned int
        if (isset($oldData['secret'])) {
            while ($oldData['secret'] == $newSecret) {
                $newSecret = mt_rand(0, $maxRand);
            }
        }
        
        foreach ($tmpname as $size => $file) {
            $privacy = ($size == '_h') ? Zend_Service_Amazon_S3::S3_ACL_PRIVATE :
            Zend_Service_Amazon_S3::S3_ACL_PUBLIC_READ;
            
            $picAddr = $config['services']['S3']['headshotsBucket']."/".$userInfo['id'].'-'.$newSecret.$size.'.jpg';
            
            $meta = array(Zend_Service_Amazon_S3::S3_ACL_HEADER =>
              $privacy,
              "Content-Type" => Zend_Service_Amazon_S3::getMimeType($picAddr), //watch out, it is just a workaround due to problem of the Zend_Service_Amazon_S3 class
              "Cache-Control" => "max-age=37580000, public",
              "Expires" => "Thu, 10 May 2029 00:00:00 GMT"
            );
            $s3->putFile($file, $picAddr, $meta);
            
            @unlink($file);
        }
        
        $newAvatarInfo = serialize(array("sizes" => $sizesInfo, "secret" => $newSecret));
        $people->update(array("avatarInfo" => $newAvatarInfo), $people->getAdapter()->quoteInto("id = ?", $userInfo['id']));
        
        @unlink($originalfile);
        
        //delete the old files
        $this->deleteFiles($userInfo);
        
        return true;
    }
}
