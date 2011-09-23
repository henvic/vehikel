<?php

class Ml_Model_Contacts extends Ml_Model_AccessSingleton
{
    protected static $_dbTableName = "contacts";
    
    /**
     * Singleton instance
     *
     */
    protected static $_instance = null;
    
    const RELATIONSHIP_TYPE_NONE = -1;
    const RELATIONSHIP_TYPE_CONTACT = 0;
    const RELATIONSHIP_TYPE_FRIEND = 1;
    
    public function getReverseContactsPage($uid, $perPage, $page)
    {
        return $this->getContactsPage($uid, $perPage, $page, true);
    }
    
    public function getContactsPage($uid, $perPage, $page, $reverse = false)
    {
        $people = Ml_Model_People::getInstance();
        
        if ($reverse) {
            $uidF = 'has';
            $hasF = 'uid';
        } else {
            $uidF = 'uid';
            $hasF = 'has';
        }
        
        $select = $this->_dbTable->select();
        $select
        ->where($this->_dbTable->getTableName() . "." . $uidF . " = ?", $uid)
        ->order($this->_dbTable->getTableName() . ".since DESC");
        
        $people->joinDbTableInfo($select, $this->_dbTable->getTableName(), $hasF);
        
        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($perPage);
        
        return $paginator;
    }
    
    public function setRelationship($uid, $contactId, $type = self::RELATIONSHIP_TYPE_CONTACT)
    {
        if ($type == self::RELATIONSHIP_TYPE_NONE) {
            $this->_dbTable->delete($this->_dbAdapter
            ->quoteInto("uid = ? AND ", $uid) . 
            $this->_dbAdapter->quoteInto("has = ?", $contactId));
        } else {
            $this->_dbAdapter->query(
            "INSERT IGNORE INTO `" . $this->_dbTable->getTableName() .
             "` (uid, has, friend) SELECT ?, ?, ? FROM DUAL WHERE not exists (select * from `ignore` where ignore.uid = ? AND ignore.ignore = ?)", 
            array($uid, $contactId, $type, $contactId, $uid));
        }
    }
    
    public function getRelationship($uid, $has)
    {
        $query = $this->_dbTable->select()->where("uid = ?", $uid)
        ->where("has = ?", $has);
        
        $relationship = $this->_dbAdapter->fetchRow($query);
        
        $reverseQuery = $this->_dbTable->select()->where("has = ?", $uid)
        ->where("uid = ?", $has);
        
        $reverseRelationship = $this->_dbAdapter->fetchRow($reverseQuery);
        
        $relationship['reverse'] = $reverseRelationship;
        
        return $relationship;
    }
    
    public static function relationshipForm()
    {
        $registry = Zend_Registry::getInstance();
        
        static $form = '';
        
        if (! is_object($form)) {
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $userInfo = $registry->get("userInfo");
            
            $form = new Ml_Form_Relationship(array(
                'action' => $router->assemble(array("username" => $userInfo['alias']), "contact_relationship"),
                'method' => 'post',
            ));
            
        }
        return $form;
    }
    
    public function getRecentUploads($uid)
    {
        //@todo cache contacts
        $select = $this->_dbTable->select();
        
        $select
        ->where($this->_dbTable->getTableName().".uid = ?", $uid);
        
        $select->from($this->_dbTable->getTableName());
        $select->setIntegrityCheck(false);
        
        //@todo add privacy rules
        $select->joinRight("share", "share.byUid = " .
        $this->_dbTable->getTableName() .
         ".has AND DATE_ADD(uploadedTime, INTERVAL 5 DAY) > CURRENT_TIMESTAMP AND share.id = (SELECT MAX(s2.id) FROM share s2 WHERE s2.byUid = share.byUid)",
         array("share.id as share.id", "share.title as share.title", "share.fileSize as share.fileSize", "share.short as share.short", "share.views as share.views"));
        
        $select->joinRight("people", "people.id = " .
         $this->_dbTable->getTableName() . ".has",
         array("people.id as people.id", "people.alias as people.alias", "people.name as people.name", "people.avatarInfo as people.avatarInfo"));
         
        $select->order("share.uploadedTime DESC");
        
        $select->limitPage(1, 15);
        
        $recentUploads = $this->_dbTable->fetchAll($select);
        
        if (is_object($recentUploads)) {
            return $recentUploads->toArray();
        }
        return array();
        
        return $this->contactsUploads($uid);
    }
}
