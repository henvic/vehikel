<?php
class Ml_Model_Share extends Ml_Model_Db_Table
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
    
    protected $_name = "share";
    
    public function getInfo($shareId, $uid)
    {
        // tips for users:
        // http://www.thesitewizard.com/webdesign/create-good-filenames.shtml
        $select = $this->select()
         ->where("id = ?", $shareId)->where("byUid = ?", $uid);
         
        $shareInfo = $this->fetchRow($select);
        
        if (!is_object($shareInfo)) {
            return false;
        }
        
        $data = $shareInfo->toArray();
        
        return $data;
    }
    
    public function getPages($uid, $perPage, $page)
    {
        $select = $this->select();
        
        $select->where("byUid = ?", $uid)
        ->order("uploadedTime DESC");
        
        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($perPage);
        
        return $paginator;
    }
}