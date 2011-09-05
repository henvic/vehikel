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
    {}
    
    
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    public function getReverseContactsPage($uid, $per_page, $page)
    {
        return $this->getContactsPage($uid, $per_page, $page, true);
    }
    
    public function getContactsPage($uid, $per_page, $page, $reverse = false)
    {
        if($reverse) {
            $uid_f = 'has';
            $has_f = 'uid';
        } else {
            $uid_f = 'uid';
            $has_f = 'has';
        }
        
        $select = $this->select();
        $select
        ->where($this->_name.".".$uid_f." = ?", $uid)
        ->order($this->_name.".since DESC")
        ;
        
        //$select->joinLeft("contacts as reverse", "reverse.uid = contacts.has", array("reverse.since as reverse.since", "reverse.friend as reverse.friend"));
        //doesn't work 'faking the select with a VIEW' neither. Needs other structure found in joinPeopleInfo(), etc.
        
        $this->joinPeopleInfo($select, $this->_name, $has_f);
        
        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($per_page);
        
        return $paginator;
    }
    
    public function getInfo($uid, $has)
    {
        $query = $this->select()->where("uid = ?", $uid)
        ->where("has = ?", $has)
        ;
        
        $relationship = $this->getAdapter()->fetchRow($query);
        
        $reverseQuery = $this->select()->where("has = ?", $uid)
        ->where("uid = ?", $has)
        ;
        
        $reverseRelationship = $this->getAdapter()->fetchRow($reverseQuery);
        
        $relationship['reverse'] = $reverseRelationship;
        
        return $relationship;
    }
}
