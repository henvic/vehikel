<?php
class ML_Contacts extends ML_getModel
{
    protected $_name = "contacts";
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
    
    public function getReverseContactsPage($uid, $perPage, $page)
    {
        return $this->getContactsPage($uid, $perPage, $page, true);
    }
    
    public function getContactsPage($uid, $perPage, $page, $reverse = false)
    {
        if ($reverse) {
            $uidF = 'has';
            $hasF = 'uid';
        } else {
            $uidF = 'uid';
            $hasF = 'has';
        }
        
        $select = $this->select();
        $select
        ->where($this->_name.".".$uidF." = ?", $uid)
        ->order($this->_name.".since DESC");
        
        $this->joinPeopleInfo($select, $this->_name, $hasF);
        
        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($perPage);
        
        return $paginator;
    }
    
    public function getInfo($uid, $has)
    {
        $query = $this->select()->where("uid = ?", $uid)
        ->where("has = ?", $has);
        
        $relationship = $this->getAdapter()->fetchRow($query);
        
        $reverseQuery = $this->select()->where("has = ?", $uid)
        ->where("uid = ?", $has);
        
        $reverseRelationship = $this->getAdapter()->fetchRow($reverseQuery);
        
        $relationship['reverse'] = $reverseRelationship;
        
        return $relationship;
    }
}
