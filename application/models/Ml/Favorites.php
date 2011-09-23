<?php

class Ml_Model_Favorites extends Ml_Model_AccessSingleton
{
    protected static $_dbTableName = "favorites";
    
    /**
     * Singleton instance
     *
     * @var Ml_Model_Favorites
     */
    protected static $_instance = null;

    public function count($shareId)
    {
        $query = $this->_dbTable->select()
         ->from($this->_dbTable->getTableName(), 'count(*)')
         ->where("share = ?", $shareId);
        
        return $this->_dbAdapter->fetchOne($query);
    }
    
    /**
     * 
     * Tells if a user has favorited a item
     * @param big int $uid
     * @param big int $id
     * @return false on failure, favorite info data on success
     */
    public function isFavorite($uid, $id)
    {
        $select = $this->_dbTable->select();
        $select
        ->where("uid = ?", $uid)
        ->where("share = ?", $id);
        
        $result = $this->_dbTable->fetchAll($select);
        
        if (! is_object($result)) {
            return false;
        }
        
        $data = $result->toArray();
        
        if (empty($data)) {
            return false;
        } else {
            return $data[0];
        }
    }
    
    public function getUserPage($uid, $perPage, $page)
    {
        $share = Ml_Model_Share::getInstance();
        
        $select = $this->_dbTable->select();
        
        $select->setIntegrityCheck(false);
        
        $select->order("E.timestamp DESC");
        
        $select->from(array('E' => 'favorites'),
                      array("id", "share", "byUid", "timestamp"));
        
        $select->joinInner("share", "`E`.`share` = `share`.`id`",
         array("title as share.title",
               "fileSize as share.fileSize",
               "short as share.short",
               "filename as share.filename"));
        
        $select->joinInner("people as D", "`E`.`byUid` = `D`.`id`",
         array("name as people.name", "alias as people.alias",
         "avatarInfo as people.avatarInfo"));
        
        $select->where("`E`.`uid` = ?", $uid);
        
        /*
        SELECT
`E`.`id`,
`E`.`share`,
`E`.`byUid`,
`E`.`timestamp`,
`share`.`title` as `share.title`,
`share`.`fileSize` as `share.fileSize`,
`share`.`short` as `share.short`,
`share`.`filename` as `share.filename`,
`D`.`alias` as `people.alias`,
`D`.`name` as `people.name`,
`D`.`avatarInfo` as `people.avatarInfo`
FROM `favorites` AS `E` INNER JOIN `people` as `D` ON `E`.`byUid` = `D`.`id` INNER JOIN `share` ON `E`.`share` = `share`.`id`
WHERE `E`.`uid` = '33' ORDER BY `E`.`timestamp` DESC
        
        */
        
        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($perPage);
        
        return $paginator;
    }
    
    public function getSharePage($shareId, $perPage, $page)
    {
        $people = Ml_Model_People::getInstance();
        
        $tableName = $this->_dbTable->getTableName();
        
        $select = $this->_dbTable->select();
        $select->where($tableName . ".share = ?", $shareId)
        ->order($tableName . ".timestamp DESC");
        
        $people->joinDbTableInfo($select, $tableName, "uid");
        
        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($perPage);
        
        return $paginator;
    }
    
    /**
     * 
     * Removes favorites that a usar has from a single user
     * @param big int $uid
     * @param big int $ignoreUid
     */
    public function deleteFavoritesFrom($uid, $ignoreUid)
    {
        $this->_dbTable->delete($this->_dbAdapter
            ->quoteInto('`uid` = ?', $ignoreUid) .
            $this->_dbAdapter->quoteInto(' AND `byUid` = ?', $uid));
        $this->_dbTable->delete($this->_dbAdapter
            ->quoteInto('`byUid` = ?', $ignoreUid) .
            $this->_dbAdapter->quoteInto(' AND `uid` = ?', $uid));
    }
    
    /**
     * 
     * Favorite item
     * @param big int $uid
     * @param big int $shareId item id
     * @param big int $byUid owner of the item
     */
    public function favorite($uid, $shareId, $byUid)
    {
        $insert = $this->_dbAdapter->query("INSERT IGNORE INTO `" . $this->_dbAdapter
        ->quoteTableAs(($this->_dbTable->getTableName())) .
        "` (uid, share, byUid) SELECT ?, ?, ? FROM DUAL WHERE not exists (select * from `ignore` where ignore.uid = ? AND ignore.ignore = ?)", 
        array($uid, $shareId, $byUid, $byUid, $uid));
        
        if ($insert) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 
     * Unfavorite item
     * @param big int $uid
     * @param big int $shareId item id
     */
    public function unfavorite($uid, $shareId)
    {
        $delete = $this->_dbTable
        ->delete($this->_dbAdapter->quoteInto("uid = ?", $uid) .
        $this->_dbTable->quoteInto(" AND share = ?", $shareId));
        
        if ($delete) {
            return true;
        } else {
            return false;
        }
    }
    
    public static function form()
    {
        static $form = '';
        $registry = Zend_Registry::getInstance();
        if (! is_object($form)) {
            $form = new Ml_Form_Favorite(array(
                'method' => 'post',
            ));
        }
        
        $form->setDefault("hash", $registry->get('globalHash'));

        return $form;
    }
}
