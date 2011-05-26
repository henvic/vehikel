<?php
class ML_Recent
{
	public function contactsUploads($uid)
	{
		//@todo must cache
		$Contacts = ML_Contacts::getInstance();
		$select = $Contacts->select();
		
		$select
		->where($Contacts->getTableName().".uid = ?", $uid)
		;
		
		$select->from($Contacts->getTableName());
		$select->setIntegrityCheck(false);
		
		//privacy rules should be put here in this next $cond joinRight when offering them
		$select->joinRight("share", "share.byUid = ".$Contacts->getTableName().".has AND DATE_ADD(uploadedTime, INTERVAL 5 DAY) > CURRENT_TIMESTAMP AND share.id = (SELECT MAX(s2.id) FROM share s2 WHERE s2.byUid = share.byUid)", array("share.id as share.id", "share.title as share.title", "share.fileSize as share.fileSize", "share.short as share.short", "share.views as share.views"));
		$select->joinRight("people", "people.id = ".$Contacts->getTableName().".has", array("people.id as people.id", "people.alias as people.alias", "people.name as people.name", "people.avatarInfo as people.avatarInfo"));
		$select->order("share.uploadedTime DESC");
		
		$select->limitPage(1, 15);
		
		$recent_uploads = $Contacts->fetchAll($select);
		
		return (is_object($recent_uploads)) ? $recent_uploads->toArray() : array();
	}
	
	public function commentsInSharesOf($uid)
	{
		$Comments = ML_Comments::getInstance();
		$select = $Comments->select();
		
		$select
		->where($Comments->getTableName().".byUid = ?", $uid)
		;
		
		$select->from($Comments->getTableName());
		$select->setIntegrityCheck(false);
		
		$select->joinRight("share", "share.id = ".$Comments->getTableName().".share AND DATE_ADD(comments.lastModified, INTERVAL 3 DAY) > CURRENT_TIMESTAMP AND share.id = (SELECT MAX(s2.id) FROM share s2 WHERE s2.byUid = share.byUid)", array("share.id as share.id", "share.title as share.title", "share.fileSize as share.fileSize", "share.short as share.short", "share.views as share.views"));
		/*If I use I need to care about people_deleted also: $select->joinRight("people", "people.id = ".$Comments->getTableName().".uid", array("people.id as people.id", "people.alias as people.alias", "people.name as people.name", "people.avatarInfo as people.avatarInfo"));*/
		$select->order("comments.lastModified DESC");
		
		$select->group("share.id");
		
		$select->limitPage(1, 10);
		
		$recent_comments = $Comments->fetchAll($select);
		
		return (is_object($recent_comments)) ? $recent_comments->toArray() : array();
	}
	/*
	public function commentsLeftBy($uid, $only_elsewhere = false)
	{
		$Comments = ML_Comments::getInstance();
		$select = $Comments->select();
		
		$select
		->where($Comments->getTableName().".uid = ?", $uid)
		;
		
		if($only_elsewhere)
		{
			$select->where($Comments->getTableName().".byUid != ?", $uid);
		}
		
		$select->from($Comments->getTableName());
		$select->setIntegrityCheck(false);
		
		$select->joinRight("share", "share.id = ".$Comments->getTableName().".share AND DATE_ADD(comments.lastModified, INTERVAL 8 DAY) > CURRENT_TIMESTAMP AND share.id = (SELECT MAX(s2.id) FROM share s2 WHERE s2.byUid = share.byUid)", array("share.id as share.id", "share.title as share.title", "share.fileSize as share.fileSize", "share.short as share.short", "share.views as share.views"));
		$select->joinRight("people", "people.id = ".$Comments->getTableName().".byUid", array("people.id as people.id", "people.alias as people.alias", "people.name as people.name", "people.avatarInfo as people.avatarInfo"));
		$select->order("comments.lastModified DESC");
		
		$select->limitPage(1, 10);
		
		$recent_comments = $Comments->fetchAll($select);
		
		return (is_object($recent_comments)) ? $recent_comments->toArray() : array();
	}
	*/
}