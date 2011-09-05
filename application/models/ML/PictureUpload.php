<?php
/**
 * @todo check if there isn't a security breach
 * with the filename because of the phMagick
 * 
 * 
 * shoudln't escapeshellcmd be used somewhere?
 */

require_once LIBRARY_PATH . '/phMagick/MODIFIEDphMagick.php';

class ML_PictureUpload extends ML_Picture
{
    public function _getPictureForm()
    {
        static $form = '';
        
        if(!is_object($form))
        {
            require_once APPLICATION_PATH . '/forms/Picture.php';
            
            $form = new Form_Picture(array(
                'action' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), "accountpicture"),
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
        if(!isset($avatarInfo['secret'])) {
            return false;
        }
        
        $s3 = new Zend_Service_Amazon_S3($config['services']['S3']['key'], $config['services']['S3']['secret']);
        
        foreach($this->Sizes as $sizeInfo)
        {
            $s3->removeObject($config['services']['S3']['headshotsBucket']."/".$userInfo['id'].'-'.$avatarInfo['secret'].$sizeInfo[1].'.jpg');
        }
        
        return true;
    }
    
    public function deleteAvatar($userInfo)
    {
        $this->deleteFiles($userInfo);
        
        $People = ML_People::getInstance();
        
        $People->update(array("avatarInfo" => serialize(array())), $People->getAdapter()->quoteInto("id = ?", $userInfo['id']));
        
        return true;
    }
    
    public function setAvatar($userInfo, $originalfile)
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        $People = ML_People::getInstance();
        
        $Image = new phMagick($originalfile);
        
        $s3 = new Zend_Service_Amazon_S3($config['services']['S3']['key'], $config['services']['S3']['secret']);
        
        $Image->setImageQuality(70);
        
        $dim = $Image->getDimentions();
        
        if(!$dim) return false;
        
        list($width, $height) = $dim;
        $unsharp = "-unsharp 0x0.4";
        $sizesInfo = array();
        $tmpname = array();
        foreach($this->Sizes as $sizeInfo)
        {
            $tmpname[$sizeInfo[1]] = tempnam(sys_get_temp_dir(), 'HEADSHOT');
            
            $Image->setSource($originalfile);
            $Image->setDestination($tmpname[$sizeInfo[1]]);
            
            if($sizeInfo[0] == "sq")
            {
                $size = ($height < $width) ? $height : $width;
                //@todo let the user crop using Javascript, so he/she can override the offsets (0,0)
                $Image->crop($size, $size, 0, 0, "center");
                $Image->resize($sizeInfo[3], $sizeInfo[3], $unsharp);
            } elseif($width < $sizeInfo[3] &&
                $height < $sizeInfo[3] && $sizeInfo[2] != 'huge')
                {
                    copy($Image->getSource(), $Image->getDestination());
                } else {
                ($width > $height) ? $Image->resize($sizeInfo[3], 0, $unsharp) :
                    $Image->resize(0, $sizeInfo[3], $unsharp);
            }
            
            list($widthThis, $heightThis) = $Image->getDimentions();
            $sizesInfo[$sizeInfo[0]] = array("w" => $widthThis, "h" => $heightThis);
        }
        
        $oldData = unserialize($userInfo['avatarInfo']);
        
        //I know this is insane, but I'm an unperfect perfectionist
        //I think this is for a bank account ha!
        $MAX_RAND = (mt_getrandmax() < 4294967295) ? mt_getrandmax() : 4294967295;
        $new_secret = mt_rand(0, $MAX_RAND);//unsigned int
        if(isset($oldData['secret']))
        while($oldData['secret'] == $new_secret)
        {
            $new_secret = mt_rand(0, $MAX_RAND);
        }
        
        foreach($tmpname as $size => $file)
        {
            $privacy = ($size == '_h') ? Zend_Service_Amazon_S3::S3_ACL_PRIVATE :
            Zend_Service_Amazon_S3::S3_ACL_PUBLIC_READ;
            
            $picAddr = $config['services']['S3']['headshotsBucket']."/".$userInfo['id'].'-'.$new_secret.$size.'.jpg';
            
            $meta = array(Zend_Service_Amazon_S3::S3_ACL_HEADER =>
              $privacy,
              "Content-Type" => Zend_Service_Amazon_S3::getMimeType($picAddr), //watch out, it is just a workaround due to problem of the Zend_Service_Amazon_S3 class
              "Cache-Control" => "max-age=37580000, public",
              "Expires" => "Thu, 10 May 2029 00:00:00 GMT"
            );
            $s3->putFile($file, $picAddr, $meta);
            
            @unlink($file);
        }
        
        $newAvatarInfo = serialize(array("sizes" => $sizesInfo, "secret" => $new_secret));
        $People->update(array("avatarInfo" => $newAvatarInfo), $People->getAdapter()->quoteInto("id = ?", $userInfo['id']));
        
        @unlink($originalfile);
        
        $this->deleteFiles($userInfo);//delete the old data
        
        return true;
    }
}
