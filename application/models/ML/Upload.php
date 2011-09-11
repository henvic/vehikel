<?php
class Ml_Upload extends Ml_Share
{
    protected $_editableMetadata =
     array("title", "filename", "short", "description");
    
    public function getUploadStatus($uid)
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        if (! is_natural_dbId($uid)) {
            return false;
        }
        
        $this->getAdapter()->query("set @aaaab:=0");
        
        $since = date("Y-m-00 00:00:00");
        
        $call = "select sum from (select * from(select @aaaab:=@aaaab+filesize as sum, id from upload_history where byUid = ? and timestamp >= ? order by timestamp ASC) as sizesum) as sum order by sum DESC LIMIT 1";
        
        $query = $this->getAdapter()->fetchOne($call, 
        array($uid, $since));
        
        if (! $query) {
            $query = 0;
        }
        
        $this->getAdapter()->query("set @aaaab:=0");
        
        $uploadStatus = array("bandwidth" =>
         array(
         "maxbytes" => floor($config['share']['monthlyLimit']*1024*1024),
         "usedbytes" => ceil($query),
         "remainingbytes" => floor(($config['share']['monthlyLimit']*1024*1024)-($query)) 
         ),
         "filesize" => array(
         "maxbytes" => floor($config['share']['maxFileSize']*1024*1024),));
        
         return $uploadStatus;
    }

    
    public function addFile($fileInfo, $userInfo, $privacy = false, $details = false)
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        //require_once(LIBRARY_PATH."/ML/Filters/FilenameRobot.php");
        //require_once(LIBRARY_PATH."/ML/Validators/Filename.php");
        
        $s3 = new Zend_Service_Amazon_S3($config['services']['S3']['key'], $config['services']['S3']['secret']);
        
        $filenameFilter = new Ml_Filter_FilenameRobot();
        $filenameValidator = new Ml_Validator_Filename();
        
        if (isset($details['title']) && ! empty($details['title'])) {
            $title = $details['title'];
        } else {
            $title = mb_substr(trim($fileInfo['name']), 0, 100);
            
            /* begin of gambiarra */
            $titleNameposition = mb_strrpos($title, ".");
            $titleSize = mb_strlen($title);
            if ($titleSize > 5 && $titleSize - $titleNameposition <= 5) {
                $tryTitle = mb_substr($title, 0, $titleNameposition);
                if (! empty($tryTitle) &&
                 strrpos($tryTitle, ".") < mb_strlen($tryTitle) - 4) {
                    $title = $tryTitle;
                }
            }/* end of gambiarra */
        }
        $maxRand = (mt_getrandmax() < 4294967295) ? mt_getrandmax() : 4294967295;
        $secret = mt_rand(0, $maxRand);
        $downloadSecret = mt_rand(0, $maxRand);
        
        $filename = $filenameFilter->filter($fileInfo['name']);
        
        if (! $filenameValidator->isValid($filename)) {
            $extension = $filenameFilter->filter(strchr($filename, '.'));
            
            if ($filenameValidator->isValid($extension)) {
                $filename = mt_rand() . $extension;
            } else {
                $filename = mt_rand();
            }
        }
        
        $this->getAdapter()->beginTransaction();
        
        try {
            $this->getAdapter()->insert("upload_history",
            array("byUid" => $userInfo['id'], "fileSize" => $fileInfo['size'], "filename" => $fileInfo['name']));
            
            $uploadId = $this->getAdapter()->lastInsertId("upload_history");
            if (! $uploadId) {
                throw new Exception("Can not create upload ID.");
            }
            
            $objectKey = $userInfo['alias'] . "/" . $uploadId . "-" . $downloadSecret . "/" . $filename;
            
            $put = $s3->putFile($fileInfo['tmp_name'],
             $config['services']['S3']['sharesBucket'] . "/" . $objectKey,
            
            array(
            Zend_Service_Amazon_S3::S3_ACL_HEADER =>
              Zend_Service_Amazon_S3::S3_ACL_PUBLIC_READ,
              "Content-Type" => Zend_Service_Amazon_S3::getMimeType($objectKey), //watch out, it is just a workaround due to problem of the Zend_Service_Amazon_S3 class
              'Content-Disposition' => 'attachment;',
              "x-amz-meta-id" => $uploadId,
              "x-amz-meta-uid" => $userInfo['id'],
              "x-amz-meta-username" => $userInfo['alias']
              ));
              
            if (! $put) {
                throw new Exception("Could not upload to storage service.");
            }
            
            $getInfo = $s3->getInfo($objectKey);
            
            //If for error we can't retrieve the md5 from the s3 server...
            if (! $getInfo) {
                $md5 = md5_file($fileInfo['tmp_name']);
            } else {
                $md5 = $getInfo['etag'];
            }
            
            if (! isset($details['short'])) {
                $details['short'] = '';
            }
            if (! isset($details['description'])) {
                $details['description'] = '';
            }
            
            $this->insert(array("id" => $uploadId,
                        "byUid" => $userInfo['id'],
                        "secret" => $secret,
                        "download_secret" => $downloadSecret,
                        "privacy" => $privacy,
                        "title" => $title,
                        "filename" => $filename,
                        "short" => $details['short'],
                        "description" => $details['description'],
                        "type" => mb_substr($fileInfo['type'], 0, 50),
                        "fileSize" => $fileInfo['size'], //in bytes
                        "md5" => $md5,));
            
            if (! $this->getAdapter()->lastInsertId()) {
                throw new Exception("Could not create insert for new file.");
            }
            
            $this->getAdapter()->commit();
            
            return $uploadId;
        } catch(Exception $e)
        {
            $this->getAdapter()->rollBack();
            throw $e;
        }
    }
    
    public function setMeta($userInfo, $shareInfo, $metaData, $errorHandle = false)
    {
        $registry = Zend_Registry::getInstance();
        
        $config = $registry->get("config");
        
        if ($userInfo['id'] != $shareInfo['byUid']) {
            throw new Exception("User is not the owner of the share.");
        }
        
        $changeData = array();
        
        if ($errorHandle) {
            foreach ($this->editableMetadata as $what) {
                if (empty($errorHandle[$what]) &&
                 $metaData[$what] != $shareInfo[$what]) {
                    $changeData[$what] = $metaData[$what];
                }
            }
        } else {
            $changeData = $metaData;
        }
        
        if (empty($changeData)) {
            return false;
        }
        
        if (isset($changeData['filename'])) {
            $s3 = new Zend_Service_Amazon_S3($config['services']['S3']['key'], $config['services']['S3']['secret']);
            
            $bucketPlusObjectKeyPrefix = $config['services']['S3']['sharesBucket']."/".$userInfo['alias']."/".$shareInfo['id']."-".$shareInfo['download_secret']."/";
            $source = $bucketPlusObjectKeyPrefix.$shareInfo['filename'];
            $destination = $bucketPlusObjectKeyPrefix.$changeData['filename'];
            
            $meta = array(
                        Zend_Service_Amazon_S3::S3_ACL_HEADER =>
                          Zend_Service_Amazon_S3::S3_ACL_PUBLIC_READ,
                        "x-amz-copy-source" => $source,
                        "x-amz-metadata-directive" => "COPY",
                    );
            
            $request = $s3->_makeRequest("PUT", $destination, null, $meta);
            
            if ($request->getStatus() == 200) {
                $filenameChanged = true;
            }
        }
        
        if (isset($filenameChanged) && $filenameChanged) {
            $removeFiles = new Ml_RemoveFiles();
            $removeFiles->insert(array(
                "share" => $shareInfo['id'],
                "byUid" => $shareInfo['byUid'],
                "download_secret" => $shareInfo['download_secret'],
                "filename" => $shareInfo['filename'],
                "alias" => $userInfo['alias'],
            ));
            //Using delete from the S3 Zend class here doesn't work because of a bug
            //is not working for some reason after the _makeRequest or other things I tried to COPY...
        } else {
            unset($changeData['filename']);
        }
        
        if (empty($changeData)) {
            return false;
        }
        
        if (isset($changeData['description'])) {
            $purifier = Ml_HtmlPurifier::getInstance();
            $changeData['description_filtered'] = $purifier->purify($changeData['description']);
        }
        
        $date = new Zend_Date();
        $changeData['lastChange'] = $date->get("yyyy-MM-dd HH:mm:ss");
        
        $this->update($changeData, $this->getAdapter()->quoteInto("id = ?", $shareInfo['id']));
        
        return array_merge($shareInfo, $changeData);
    }
    
    public function deleteShare($shareInfo, $userInfo)
    {    
        $removeFiles = new Ml_RemoveFiles();
        
        if (! isset($shareInfo['secret']) || ! isset($userInfo['alias'])) {
            throw new Exception("Not shareInfo or userInfo data.");
        }
        
        $removeFiles->getAdapter()->beginTransaction();
        
        try {
            $removeFiles->insert(array(
                "id" => $shareInfo['id'],
                "byUid" => $shareInfo['byUid'],
                "alias" => $userInfo['alias'],
                "download_secret" => $shareInfo['download_secret'],
                "filename" => $shareInfo['filename'],
            ));
            
            $this->delete($this->getAdapter()->quoteInto("id = ?", $shareInfo['id']));
            
            $removeFiles->getAdapter()->commit();
        } catch(Exception $e)
        {
            $removeFiles->getAdapter()->rollBack();
            throw $e;
        }
        
        return true;
    }
    
    public function form()
    {
        static $form = '';
        $registry = Zend_Registry::getInstance();

        if (! is_object($form)) {
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            require APPLICATION_PATH . '/forms/Upload.php';
             
            $form = new Form_Upload(array(
                'action' => $router->assemble(array(), "upload"),
                'method' => 'post',
            ));
        }
        
        return $form;
    }
    
    public function apiForm()
    {
        static $form = '';
        
        if (! is_object($form)) {
            require APPLICATION_PATH . '/forms/api/uploadForm.php';
            
            $form = new Form_Upload(array('method' => 'post',));
        }
        
        return $form;
    }
    
    public function deleteForm()
    {
        static $form = '';

        if (! is_object($form)) {
            $registry = Zend_Registry::getInstance();
            
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $shareInfo = $registry->get('shareInfo');
            $userInfo = $registry->get('userInfo');
            
            require APPLICATION_PATH . '/forms/DeleteShare.php';
             
            $form = new Form_DeleteShare(array('action' =>
            $router->assemble(array("username" => $userInfo['alias'],
                "share_id" => $shareInfo['id']), "deleteshare"),
                'method' => 'post'));
        }
        return $form;
    }
    
    public function editForm()
    {
        static $form = '';

        if (! is_object($form)) {
            $registry = Zend_Registry::getInstance();
            
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $shareInfo = $registry->get('shareInfo');
            $userInfo = $registry->get('userInfo');
             
            require APPLICATION_PATH . '/forms/Filepage.php';
             
            $form = new Form_Filepage(array('action'
            => $router->assemble(array("username" => $userInfo['alias'],
                "share_id" => $shareInfo['id']), "editsharepage"),
                'method' => 'post'));
        }
        
        $form->setDefault("hash", $registry->get('globalHash'));
        
        return $form;
    }
    
    public function apiSetMetaForm()
    {
        static $form = '';

        if (! is_object($form)) {
            require APPLICATION_PATH . '/forms/api/Setmeta.php';
             
            $form = new Form_Setmeta(array(
                'method' => 'post',
            ));
        }
        
        return $form;
    }
    
}