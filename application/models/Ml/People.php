<?php
class Ml_Model_People extends Ml_Model_AccessSingleton
{
    protected static $_dbTableName = "people";
    
    /**
     * Singleton instance
     *
     */
    protected static $_instance = null;
    
    public function getByUsername($username)
    {
        $select = $this->_dbTable->select()
        ->where("binary `alias` = ?", $username);
        
        return $this->_dbAdapter->fetchRow($select);
    }
    
    public function getByEmail($email)
    {
        $select = $this->_dbTable->select()
        ->where("binary `email` = ?", $email);
        
        return $this->_dbAdapter->fetchRow($select);
    }
    
    public function getById($uid)
    {
        return $this->_dbTable->getById($uid);
    }
    
    public function updateRENAME($uid, $data)
    {
        $update = $this->update($data, $this->_dbAdapter->quoteInto("id = ?", $uid));
        if ($update) {
            return true;
        }
        
        return false;
    }
    
    public function create($username, $password, $data, $confirmationInfo)
    {
        $signUp = Ml_Model_SignUp::getInstance();
        $credential = Ml_Model_Credential::getInstance();
        $profile = Ml_Model_Profile::getInstance();
        
        $this->_dbAdapter->beginTransaction();
        try {
            $signUp->delete($confirmationInfo['id']);
            
            $this->_dbTable->insert($data);
            
            $uid = $this->_dbAdapter->lastInsertId();
            
            if (! $uid) {
                throw new Exception("Failed to create user account");
            }
            
            $credential->setCredential($uid, $password);
            $profile->create($uid);
            $this->_dbAdapter->commit();
        } catch (Exception $e) {
            $this->_dbAdapter->rollBack();
            throw $e;
        }
        
        return $uid;
    }
    
    public function delete($uid)
    {
        return $this->_dbTable->delete($this->_dbAdapter->quoteInto("id = ?", $uid));
    }
    
    public function joinDbTableInfo(&$select, $parentTable, $uidField)
    {    
        $select->from($parentTable);
        $select->setIntegrityCheck(false);
        
        $select->joinLeft("people",
         "people.id = " . $parentTable . "." . $uidField,
         array("people.id as people.id",
          "people.alias as people.alias",
          "people.name as people.name",
          "people.avatarInfo as people.avatarInfo"));
         
        $select->joinLeft("people_deleted",
         "people.id IS NULL AND people_deleted.id = " .
         $parentTable . "." . $uidField,
          array("people_deleted.id as people_deleted.id",
           "people_deleted.alias as people_deleted.alias", 
           "people_deleted.name as people_deleted.name"));
    }
}
