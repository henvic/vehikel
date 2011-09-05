<?php
class ML_Ignore extends ML_getModel
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
    {}
    
    
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    protected $_name = "ignore";
    
    public function status($uid, $ignore_uid)
    {
        $select = $this->select()
        ->where("`uid` = ?", $uid)
        ->where("`ignore` = ?", $ignore_uid)
        ;
        
        $status = $this->getAdapter()->fetchRow($select);
        
        return $status;
    }
    
    public function set($uid, $ignore_uid)
    {
        $Contacts = ML_Contacts::getInstance();
        $Favorites = ML_Favorites::getInstance();
        
        $this->getAdapter()->beginTransaction();
        
        try {
            $Contacts->delete($this->getAdapter()->quoteInto('`uid` = ?', $uid).$this->getAdapter()->quoteInto(' AND `has` = ?', $ignore_uid));
            $Contacts->delete($this->getAdapter()->quoteInto('`uid` = ?', $ignore_uid).$this->getAdapter()->quoteInto(' AND `has` = ?', $uid));
            $Favorites->delete($this->getAdapter()->quoteInto('`uid` = ?', $ignore_uid).$this->getAdapter()->quoteInto(' AND `byUid` = ?', $uid));
            $Favorites->delete($this->getAdapter()->quoteInto('`byUid` = ?', $ignore_uid).$this->getAdapter()->quoteInto(' AND `uid` = ?', $uid));
            
            $this->insert(array("uid" => $uid, "ignore" => $ignore_uid));
            $this->getAdapter()->commit();
        } catch(Exception $e)
        {
            $Contacts->getAdapter()->rollBack();
            throw $e;
        }
        
        return $this->getAdapter()->lastInsertId();
    }
    
    public function remove($uid, $ignore_uid)
    {
          return $this->delete($this->getAdapter()->quoteInto('`uid` = ?', $uid).$this->getAdapter()->quoteInto(' AND `ignore` = ?', $ignore_uid));
    }
    
    public function getIgnorePage($uid, $per_page, $page, $reverse = false)
    {
        if($reverse) {
            $uid_f = 'ignore';
            $has_f = 'uid';
        } else {
            $uid_f = 'uid';
            $has_f = 'ignore';
        }
        
        $select = $this->select();
        $select
        ->where($this->_name.".".$uid_f." = ?", $uid)
        ->order($this->_name.".timestamp DESC")
        ;
        
        $this->joinPeopleInfo($select, $this->_name, $has_f);
        
        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($per_page);
        
        return $paginator;
    }
}