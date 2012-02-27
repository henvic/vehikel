<?php
class Ml_Model_Share extends Ml_Model_AccessSingleton
{
    protected static $_editableMetadata =
    array("title", "filename", "short", "description");
    
    protected static $_dbTableName = "share";
    
    /**
     * Singleton instance
     *
     */
    protected static $_instance = null;
    
    public function getInfo($shareId, $uid)
    {
        // tips for users:
        // http://www.thesitewizard.com/webdesign/create-good-filenames.shtml
        $select = $this->_dbTable->select()
         ->where("id = ?", $shareId)->where("byUid = ?", $uid);
         
        $shareInfo = $this->_dbAdapter->fetchRow($select);
        
        return $shareInfo;
    }
    
    public function getPages($uid, $perPage, $page)
    {
        $select = $this->_dbTable->select();
        
        $select->where("byUid = ?", $uid)
        ->order("uploadedTime DESC");
        
        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($perPage);
        
        return $paginator;
    }
    
    public function getUploadStatus($uid)
    {
        $numbers = new Ml_Model_Numbers();
        
        $config = self::$_registry->get("config");
        
        if (! $numbers->isNaturalDbId($uid)) {
            return false;
        }
        
        $this->_dbAdapter->query("set @aaaab:=0");
        
        $since = date("Y-m-00 00:00:00");
        
        $call = "select sum from (select * from(select @aaaab:=@aaaab+filesize as sum, id from upload_history where byUid = ? and timestamp >= ? order by timestamp ASC) as sizesum) as sum order by sum DESC LIMIT 1";
        
        $query = $this->_dbAdapter->fetchOne($call, 
        array($uid, $since));
        
        if (! $query) {
            $query = 0;
        }
        
        $this->_dbAdapter->query("set @aaaab:=0");
        
        $uploadStatus = array("bandwidth" =>
         array(
         "maxbytes" => floor($config['share']['monthlyLimit'] * 1024 * 1024),
         "usedbytes" => ceil($query),
         "remainingbytes" =>
         floor(($config['share']['monthlyLimit'] * 1024 * 1024) - ($query)) 
         ),
         "filesize" => array(
         "maxbytes" => floor($config['share']['maxFileSize'] * 1024 * 1024)));
        
         return $uploadStatus;
    }
    
    public function getById($id)
    {
        $select = $this->_dbTable->select()
        ->where("binary `id` = ?", $id);
        
        return $this->_dbAdapter->fetchRow($select);
    }
    
    /**
     * 
     * Get the number of shares that a user has
     * @param big int $uid
     * @return int number of shares
     */
    public function countOfUser($uid)
    {
        $count = $this->_dbAdapter->fetchOne($this->_dbTable->select()
         ->from($this->_dbTable->getTableName(), 'count(*)')
         ->where("byUid = ?", $uid));
         
         return $count;
    }
    
    public function addFile($fileInfo, $userInfo, $privacy = false, $details = false)
    {
        $config = self::$_registry->get("config");
        
        //require_once(LIBRARY_PATH."/Ml/Filters/FilenameRobot.php");
        //require_once(LIBRARY_PATH."/Ml/Validators/Filename.php");
        
        $s3 = new Zend_Service_Amazon_S3($config['services']['S3']['key'], $config['services']['S3']['secret']);
        
        $filenameFilter = new Ml_Filter_FilenameRobot();
        $filenameValidator = new Ml_Validator_Filename();
        
        if (isset($details['title']) && ! empty($details['title'])) {
            $title = $details['title'];
        } else {
            $title = mb_substr(trim($fileInfo['name']), 0, 100);
            
            /* try to use a good initial title for the file */
            $titleNameposition = mb_strrpos($title, ".");
            $titleSize = mb_strlen($title);
            if ($titleSize > 5 && $titleSize - $titleNameposition <= 5) {
                $tryTitle = mb_substr($title, 0, $titleNameposition);
                if (! empty($tryTitle) &&
                 strrpos($tryTitle, ".") < mb_strlen($tryTitle) - 4) {
                    $title = $tryTitle;
                }
            }
        }
        //get the max value of mt_getrandmax() or the max value of the unsigned int type
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
        
        $this->_dbAdapter->beginTransaction();
        
        try {
            $this->_dbAdapter->insert("upload_history",
            array("byUid" => $userInfo['id'], "fileSize" => $fileInfo['size'], "filename" => $fileInfo['name']));
            
            $uploadId = $this->_dbAdapter->lastInsertId("upload_history");
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
            
            $this->_dbTable->insert(array("id" => $uploadId,
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
            
            if (! $this->_dbAdapter->lastInsertId()) {
                throw new Exception("Could not create insert for new file.");
            }
            
            $this->_dbAdapter->commit();
            
            return $uploadId;
        } catch(Exception $e)
        {
            $this->_dbAdapter->rollBack();
            throw $e;
        }
    }
    
    public function setMeta($userInfo, $shareInfo, $metaData, $errorHandle = false)
    {
        $config = self::$_registry->get("config");
        
        if ($userInfo['id'] != $shareInfo['byUid']) {
            throw new Exception("User is not the owner of the share.");
        }
        
        $changeData = array();
        
        if ($errorHandle) {
            foreach (self::$_editableMetadata as $what) {
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
            $s3 = new Zend_Service_Amazon_S3($config['services']['S3']['key'],
            $config['services']['S3']['secret']);
            
            $bucketPlusObjectKeyPrefix = $config['services']['S3']['sharesBucket'] . "/" .
            $userInfo['alias'] . "/" . $shareInfo['id'] . "-" . $shareInfo['download_secret'] . "/";
            
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
            $removeFiles = Ml_Model_RemoveFiles::getInstance();
            
            $removeFiles
            ->addFileGc(array("share" => $shareInfo['id'],
                "byUid" => $shareInfo['byUid'],
                "download_secret" => $shareInfo['download_secret'],
                "filename" => $shareInfo['filename'],
                "alias" => $userInfo['alias']));
            //Using delete from the S3 Zend class here doesn't work because of a bug
            //is not working for some reason after the _makeRequest or other things I tried to COPY...
        } else {
            unset($changeData['filename']);
        }
        
        if (empty($changeData)) {
            return false;
        }
        
        if (isset($changeData['description'])) {
            $purifier = Ml_Model_HtmlPurifier::getInstance();
            $changeData['description_filtered'] = $purifier->purify($changeData['description']);
        }
        
        $date = new Zend_Date();
        $changeData['lastChange'] = $date->get("yyyy-MM-dd HH:mm:ss");
        
        $this->_dbTable->update($changeData, $this->_dbAdapter->quoteInto("id = ?", $shareInfo['id']));
        
        return array_merge($shareInfo, $changeData);
    }
    
    public function deleteShare($shareInfo, $userInfo)
    {    
        $removeFiles = Ml_Model_RemoveFiles::getInstance();
        
        if (! isset($shareInfo['secret']) || ! isset($userInfo['alias'])) {
            throw new Exception("Not shareInfo or userInfo data.");
        }
        
        $this->_dbAdapter->beginTransaction();
        
        try {
            $removeFiles->addFileGc(array(
                "id" => $shareInfo['id'],
                "byUid" => $shareInfo['byUid'],
                "alias" => $userInfo['alias'],
                "download_secret" => $shareInfo['download_secret'],
                "filename" => $shareInfo['filename'],
            ));
            
            $this->_dbTable->delete($this->_dbAdapter->quoteInto("id = ?", $shareInfo['id']));
            
            $this->_dbAdapter->commit();
        } catch(Exception $e)
        {
            $this->_dbAdapter->rollBack();
            throw $e;
        }
        
        return true;
    }
    
    public static function form()
    {
        static $form = '';
        $registry = Zend_Registry::getInstance();

        if (! is_object($form)) {
            $router = Zend_Controller_Front::getInstance()->getRouter();
             
            $form = new Ml_Form_Upload(array(
                'action' => $router->assemble(array(), "upload"),
                'method' => 'post',
            ));
        }
        
        return $form;
    }
    
    public static function apiForm()
    {
        static $form = '';
        
        if (! is_object($form)) {
            $form = new Ml_Form_Api_Upload(array('method' => 'post',));
        }
        
        return $form;
    }
    
    public static function deleteForm()
    {
        static $form = '';

        if (! is_object($form)) {
            $registry = Zend_Registry::getInstance();
            
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $shareInfo = $registry->get('shareInfo');
            $userInfo = $registry->get('userInfo');
            
            $form = new Ml_Form_DeleteShare(array('action' =>
            $router->assemble(array("username" => $userInfo['alias'],
                "share_id" => $shareInfo['id']), "deleteshare"),
                'method' => 'post'));
        }
        return $form;
    }
    
    public static function editForm()
    {
        static $form = '';

        if (! is_object($form)) {
            $registry = Zend_Registry::getInstance();
            
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $shareInfo = $registry->get('shareInfo');
            $userInfo = $registry->get('userInfo');
            
            $form = new Ml_Form_Filepage(array('action'
            => $router->assemble(array("username" => $userInfo['alias'],
                "share_id" => $shareInfo['id']), "editsharepage"),
                'method' => 'post'));
        }
        
        $form->setDefault("hash", $registry->get('globalHash'));
        
        return $form;
    }
    
    public static function apiSetMetaForm()
    {
        static $form = '';

        if (! is_object($form)) {
            $form = new Ml_Form_Api_Setmeta(array('method' => 'post'));
        }
        
        return $form;
    }
}