<?php
class ML_Upload extends ML_Share
{
    protected $editable_metadata = array("title", "filename", "short", "description");
    
    public function getUploadStatus($uid)
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        if(!is_natural_dbId($uid)) return false;
        
        $this->getAdapter()->query("set @aaaab:=0");
        
        $since = date("Y-m-00 00:00:00");
        $call = "select sum from (select * from(select @aaaab:=@aaaab+filesize as sum, id from upload_history where byUid = ? and timestamp >= ? order by timestamp ASC) as sizesum) as sum order by sum DESC LIMIT 1";
        
        $query = $this->getAdapter()->fetchOne($call, 
        array($uid, $since));
        if(!$query) $query = 0;
        $this->getAdapter()->query("set @aaaab:=0");
        
        $uploadStatus = array("bandwidth" =>
         array(
         "maxbytes" => floor($config['share']['monthlyLimit']*1024*1024),
         "usedbytes" => ceil($query),
         "remainingbytes" => floor(($config['share']['monthlyLimit']*1024*1024)-($query)) 
         ),
         "filesize" => array(
         "maxbytes" => floor($config['share']['maxFileSize']*1024*1024),
         )
         );
        
         return $uploadStatus;
    }

    
    public function addFile($fileInfo, $userInfo, $privacy = false, $details = false)
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        require_once(LIBRARY_PATH."/ML/Filters/FilenameRobot.php");
        require_once(LIBRARY_PATH."/ML/Validators/Filename.php");
        
        $s3 = new Zend_Service_Amazon_S3($config['services']['S3']['key'], $config['services']['S3']['secret']);
        
        $filenameFilter = new MLFilter_FilenameRobot();
        $filenameValidator = new MLValidator_Filename();
        
        if(isset($details['title']) && !empty($details['title'])) {
            $title = $details['title'];
        } else {
            $title = mb_substr(trim($fileInfo['name']), 0, 100);
            
            /* begin of gambiarra */
            $title_nameposition = mb_strrpos($title,".");
            $title_size = mb_strlen($title);
            if($title_size > 5 && $title_size-$title_nameposition <= 5) {
                $tryTitle = mb_substr($title, 0, $title_nameposition);
                if(!empty($tryTitle) && strrpos($tryTitle, ".") < mb_strlen($tryTitle)-4) $title = $tryTitle;
            }/* end of gambiarra */
        }
        $MAX_RAND = (mt_getrandmax() < 4294967295) ? mt_getrandmax() : 4294967295;
        $secret = mt_rand(0, $MAX_RAND);
        $download_secret = mt_rand(0, $MAX_RAND);
        
        $filename = $filenameFilter->filter($fileInfo['name']);
        if(!$filenameValidator->isValid($filename)) {
            $extension = $filenameFilter->filter(strchr($filename, '.'));
            $filename = ($filenameValidator->isValid($extension)) ? $fileId.$extension : $fileId;
        }
        
        $this->getAdapter()->beginTransaction();
        
        try {
            $this->getAdapter()->insert("upload_history",
            array("byUid" => $userInfo['id'], "fileSize" => $fileInfo['size'], "filename" => $fileInfo['name']));
            
            $upload_id = $this->getAdapter()->lastInsertId("upload_history");
            if(!$upload_id) throw new Exception("Can not create upload ID.");
            
            $object_key = $userInfo['alias']."/".$upload_id."-".$download_secret."/".$filename;
            
            $put = $s3->putFile(
            $fileInfo['tmp_name'], $config['services']['S3']['sharesBucket']."/".$object_key,
            array(
            Zend_Service_Amazon_S3::S3_ACL_HEADER =>
              Zend_Service_Amazon_S3::S3_ACL_PUBLIC_READ,
              "Content-Type" => Zend_Service_Amazon_S3::getMimeType($object_key), //watch out, it is just a workaround due to problem of the Zend_Service_Amazon_S3 class
              'Content-Disposition' => 'attachment;',
              "x-amz-meta-id" => $upload_id,
              "x-amz-meta-uid" => $userInfo['id'],
              "x-amz-meta-username" => $userInfo['alias']
              ));
              
            if(!$put) throw new Exception("Could not upload to storage service.");
            
            $getInfo = $s3->getInfo($object_key);
            
            //If for error we can't retrieve the md5 from the s3 server...
            if(!$getInfo) {
                $md5 = md5_file($fileInfo['tmp_name']);
            } else $md5 = $getInfo['etag'];
            
            if(!isset($details['short'])) $details['short'] = '';
            if(!isset($details['description'])) $details['description'] = '';
            
            $this->insert(
                    array(
                        "id" => $upload_id,
                        "byUid" => $userInfo['id'],
                        "secret" => $secret,
                        "download_secret" => $download_secret,
                        "privacy" => $privacy,
                        "title" => $title,
                        "filename" => $filename,
                        "short" => $details['short'],
                        "description" => $details['description'],
                        "type" => mb_substr($fileInfo['type'], 0, 50),
                        "fileSize" => $fileInfo['size'], //in bytes
                        "md5" => $md5,
                    )
            );
            
            if(!$this->getAdapter()->lastInsertId()) {
                throw new Exception("Could not create insert for new file.");
            }
            
            $this->getAdapter()->commit();
            
            return $upload_id;
        } catch(Exception $e)
        {
            $this->getAdapter()->rollBack();
            throw $e;
        }
    }
    
    public function setMeta($userInfo, $shareInfo, $meta_data, $error_handle = false)
    {
        $registry = Zend_Registry::getInstance();
        
        $config = $registry->get("config");
        
        if($userInfo['id'] != $shareInfo['byUid']) {
            throw new Exception("User is not the owner of the share.");
        }
        
        $change_data = array();
        
        if($error_handle)
        {
            foreach($this->editable_metadata as $what)
            {
                if(empty($error_handle[$what]) && $meta_data[$what] != $shareInfo[$what]) {
                    $change_data[$what] = $meta_data[$what];
                }
            }
        } else {
            $change_data = $meta_data;
        }
        
        if(empty($change_data)) {
            return false;
        }
        
        if(isset($change_data['filename']))
        {
            $s3 = new Zend_Service_Amazon_S3($config['services']['S3']['key'], $config['services']['S3']['secret']);
            
            $bucket_plus_object_key_prefix = $config['services']['S3']['sharesBucket']."/".$userInfo['alias']."/".$shareInfo['id']."-".$shareInfo['download_secret']."/";
            $source = $bucket_plus_object_key_prefix.$shareInfo['filename'];
            $destination = $bucket_plus_object_key_prefix.$change_data['filename'];
            
            $meta = array(
                        Zend_Service_Amazon_S3::S3_ACL_HEADER =>
                          Zend_Service_Amazon_S3::S3_ACL_PUBLIC_READ,
                        "x-amz-copy-source" => $source,
                        "x-amz-metadata-directive" => "COPY",
                    );
            
            $request = $s3->_makeRequest("PUT", $destination, null, $meta);
            
            if($request->getStatus() == 200) {
                $filename_changed = true;
            }
        }
        
        if(isset($filename_changed) && $filename_changed)
        {
            $RemoveFiles = new ML_RemoveFiles();
            $RemoveFiles->insert(array(
                "share" => $shareInfo['id'],
                "byUid" => $shareInfo['byUid'],
                "download_secret" => $shareInfo['download_secret'],
                "filename" => $shareInfo['filename'],
                "alias" => $userInfo['alias'],
            ));
            //Using delete from the S3 Zend class here doesn't work because of a bug
            //is not working for some reason after the _makeRequest or other things I tried to COPY...
        } else {
            unset($change_data['filename']);
        }
        
        if(empty($change_data)) {
            return false;
        }
        
        if(isset($change_data['description']))
        {
            $purifier = ML_HtmlPurifier::getInstance();
            $change_data['description_filtered'] = $purifier->purify($change_data['description']);
        }
        
        $date = new Zend_Date();
        $change_data['lastChange'] = $date->get("yyyy-MM-dd HH:mm:ss");
        
        $this->update($change_data, $this->getAdapter()->quoteInto("id = ?", $shareInfo['id']));
        
        return array_merge($shareInfo, $change_data);
    }
    
    public function deleteShare($shareInfo, $userInfo)
    {    
        $RemoveFiles = new ML_RemoveFiles();
        
        if(!isset($shareInfo['secret']) || !isset($userInfo['alias']))
        {
            throw new Exception("Not shareInfo or userInfo data.");
        }
        
        $RemoveFiles->getAdapter()->beginTransaction();
        
        try {
            $RemoveFiles->insert(array(
                "id" => $shareInfo['id'],
                "byUid" => $shareInfo['byUid'],
                "alias" => $userInfo['alias'],
                "download_secret" => $shareInfo['download_secret'],
                "filename" => $shareInfo['filename'],
            ));
            
            $this->delete($this->getAdapter()->quoteInto("id = ?", $shareInfo['id']));
            
            $RemoveFiles->getAdapter()->commit();
        } catch(Exception $e)
        {
            $RemoveFiles->getAdapter()->rollBack();
            throw $e;
        }
        
        return true;
    }
    
    
    
    public function _apiSetMetaForm()
    {
        static $form = '';

        if(!is_object($form))
        {
            require_once APPLICATION_PATH . '/forms/api/Setmeta.php';
             
            $form = new Form_Setmeta(array(
                //
                'method' => 'post',
            ));
        }
        
        return $form;
    }
    
}