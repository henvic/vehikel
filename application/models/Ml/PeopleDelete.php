<?php
class Ml_Model_PeopleDelete extends Ml_Model_AccessSingleton
{
    protected static $_dbTableName = "people_deleted";
    
    /**
     * Singleton instance
     *
     */
    protected static $_instance = null;
    
    public static function deleteAccountForm()
    {
        static $form = '';
        
        if (! is_object($form)) {
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $form = new Ml_Form_DeleteAccount(array(
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
        
        $people = Ml_Model_People::getInstance();
        $share = Ml_Model_Share::getInstance();
        $removeFiles = Ml_Model_RemoveFiles::getInstance();
        $picture = new Ml_Model_PictureUpload();
        
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
        
        $this->_dbAdapter->beginTransaction();
        
        try {
            $picture->deleteFiles($userInfo);
            
            $removeFiles->addFilesGc($userInfo['id'], $userInfo['alias']);
            
            $this->_dbAdapter->query("INSERT INTO " . $this->_dbAdapter
            ->quoteTableAs($this->_dbTable->getTableName()) .
            " SELECT id, alias, email, membershipdate, name, private_email, CURRENT_TIMESTAMP as delete_timestamp from people where " .
            $this->_dbAdapter->quoteInto("id = ?", $userInfo['id']));
            
            $people->delete($userInfo['id']);
            
            $this->_dbAdapter->commit();
        } catch (Exception $e) {
            $this->_dbAdapter->rollBack();
            throw $e;
        }
        
        return true;
    }
}