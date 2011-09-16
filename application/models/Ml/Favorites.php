<?php
class Ml_Model_Favorites extends Ml_Model_Db_Table
{
    /**
     * Singleton instance
     *
     * @var Ml_Model_Favorites
     */
    protected static $_instance = null;
    
    
    /**
     * Singleton pattern implementation makes "new" unavailable
     *
     * @return void
     */
    //protected function __construct()
    //{
    //}

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
    
    protected $_name = "favorites";

    public function count($shareId)
    {
        $query = $this->select()
         ->from($this->_name, 'count(*)')
         ->where("share = ?", $shareId);
        
        return $this->getAdapter()->fetchOne($query);
    }
    
    public function isFavorite($uid, $id)
    {
        $select = $this->select();
        $select->where("uid = ?", $uid);
        $select->where("share = ?", $id);
        $result = $this->fetchAll($select);
        if(!is_object($result)) return false;
        $data = $result->toArray();
        if(empty($data)) return false;
        else return $data[0];
    }
    
    public function getUserPage($uid, $perPage, $page)
    {
        $share = Ml_Model_Share::getInstance();
        
        $select = $this->select();
        
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
        
        $select = $this->select();
        $select->where($this->_name.".share = ?", $shareId)
        ->order($this->_name.".timestamp DESC");
        
        $this->joinPeopleInfo($select, $this->_name, "uid");
        
        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($perPage);
        
        return $paginator;
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
