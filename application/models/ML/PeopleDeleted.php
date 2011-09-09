<?php
class ML_PeopleDeleted extends ML_getModel
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
    
    protected $_name = "people_deleted";
    
    public function _getDeleteAccountForm()
    {
        static $form = '';
        
        if (! is_object($form)) {
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            require APPLICATION_PATH . '/forms/DeleteAccount.php';
            
            $form = new Form_DeleteAccount(array(
                'action' => $router->assemble(array(), "accountdelete"),
                'method' => 'post',
            ));
        }
        return $form;
    }
    
    /* userInfo_serialized_sha1: the md5sum applied to the array serialized */
    public function deleteAccount($userInfo, $userInfoSerializedHashed)
    {
        $registry = Zend_Registry::getInstance();
        
        $people = ML_People::getInstance();
        $share = ML_Share::getInstance();
        $removeFiles = new ML_RemoveFiles();
        $picture = new ML_PictureUpload();
        
        if (! is_array($userInfo) || ! isset($userInfo['alias'])) {
            throw new Exception("Invalid userInfo data.");
        }
        
        //flag set to true when authorized to do so, least security resource
        if (! $registry->isRegistered("canDeleteAccount")) {
            throw new Exception("Not authorized to delete account.");
        }
        
        if (sha1(serialize($userInfo)) != $userInfoSerializedHashed) {
            throw new Exception("userInfo and serialized data doesn't match.");
        }
        
        $this->getAdapter()->beginTransaction();
        
        try {
            $picture->deleteFiles($userInfo);
            
            $removeFiles->getAdapter()
            ->query("INSERT INTO `" .
            $removeFiles->getTableName() .
            "` (`id`, `byUid`, `download_secret`, `filename`, `alias`, `timestamp`) SELECT id, byUid, download_secret, filename, " .
            $removeFiles->getAdapter()
            ->quoteInto("?", $userInfo['alias']) . " as alias, CURRENT_TIMESTAMP FROM `share` where " .
            $removeFiles->getAdapter()
            ->quoteInto("byUid = ?", $userInfo['id']));
            
            $this->getAdapter()->query("INSERT INTO `" . $this->getTableName() .
            "` SELECT id, alias, email, membershipdate, name, private_email, CURRENT_TIMESTAMP as delete_timestamp from people where " .
            $this->getAdapter()->quoteInto("id = ?", $userInfo['id']));
            
            $people->delete($people
            ->getAdapter()
            ->quoteInto("id = ?", $userInfo['id']));
            
            $this->getAdapter()->commit();
        } catch(Exception $e)
        {
            $this->getAdapter()->rollBack();
            throw $e;
        }
        
        return true;
    }
}