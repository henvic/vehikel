<?php
class ML_Recent
{
    public function contactsUploads($uid)
    {
        //@todo cache contacts
        $contacts = ML_Contacts::getInstance();
        $select = $contacts->select();
        
        $select
        ->where($contacts->getTableName().".uid = ?", $uid);
        
        $select->from($contacts->getTableName());
        $select->setIntegrityCheck(false);
        
        //@todo add privacy rules
        $select->joinRight("share", "share.byUid = " .
        $contacts->getTableName() .
         ".has AND DATE_ADD(uploadedTime, INTERVAL 5 DAY) > CURRENT_TIMESTAMP AND share.id = (SELECT MAX(s2.id) FROM share s2 WHERE s2.byUid = share.byUid)",
         array("share.id as share.id", "share.title as share.title", "share.fileSize as share.fileSize", "share.short as share.short", "share.views as share.views"));
        
        $select->joinRight("people", "people.id = " .
         $contacts->getTableName() . ".has",
         array("people.id as people.id", "people.alias as people.alias", "people.name as people.name", "people.avatarInfo as people.avatarInfo"));
         
        $select->order("share.uploadedTime DESC");
        
        $select->limitPage(1, 15);
        
        $recentUploads = $contacts->fetchAll($select);
        
        if (is_object($recentUploads)) {
            return $recentUploads->toArray();
        }
        return array();
    }
    
    public function commentsInSharesOf($uid)
    {
        $comments = ML_Comments::getInstance();
        $select = $comments->select();
        
        $select
        ->where($comments->getTableName().".byUid = ?", $uid);
        
        $select->from($comments->getTableName());
        $select->setIntegrityCheck(false);
        
        $select->joinRight("share", "share.id = " . $comments->getTableName() .
         ".share AND DATE_ADD(comments.lastModified, INTERVAL 3 DAY) > CURRENT_TIMESTAMP AND share.id = (SELECT MAX(s2.id) FROM share s2 WHERE s2.byUid = share.byUid)",
         array("share.id as share.id", "share.title as share.title", "share.fileSize as share.fileSize", "share.short as share.short", "share.views as share.views"));
         
        /* (?) I need to care about people_deleted also: $select->joinRight("people", "people.id = ".$comments->getTableName().".uid", array("people.id as people.id", "people.alias as people.alias", "people.name as people.name", "people.avatarInfo as people.avatarInfo"));*/
         
        $select->order("comments.lastModified DESC");
        
        $select->group("share.id");
        
        $select->limitPage(1, 10);
        
        $recentComments = $comments->fetchAll($select);
        
        if (is_object($recentComments)) {
            return $recentComments->toArray();
        }
        return array();
    }
    /*
    public function commentsLeftBy($uid, $only_elsewhere = false)
    {
        $comments = ML_Comments::getInstance();
        $select = $comments->select();
        
        $select
        ->where($comments->getTableName().".uid = ?", $uid)
        ;
        
        if($only_elsewhere)
        {
            $select->where($comments->getTableName().".byUid != ?", $uid);
        }
        
        $select->from($comments->getTableName());
        $select->setIntegrityCheck(false);
        
        $select->joinRight("share", "share.id = ".$comments->getTableName().".share AND DATE_ADD(comments.lastModified, INTERVAL 8 DAY) > CURRENT_TIMESTAMP AND share.id = (SELECT MAX(s2.id) FROM share s2 WHERE s2.byUid = share.byUid)", array("share.id as share.id", "share.title as share.title", "share.fileSize as share.fileSize", "share.short as share.short", "share.views as share.views"));
        $select->joinRight("people", "people.id = ".$comments->getTableName().".byUid", array("people.id as people.id", "people.alias as people.alias", "people.name as people.name", "people.avatarInfo as people.avatarInfo"));
        $select->order("comments.lastModified DESC");
        
        $select->limitPage(1, 10);
        
        $recentComments = $comments->fetchAll($select);
        
        if (is_object($recentComments)) {
            return $recentComments->toArray();
        }
        return array();
    }
    */
}