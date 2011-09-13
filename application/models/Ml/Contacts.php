<?php
class Ml_Model_Contacts extends Ml_Model_Db_Table
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
    
    public function relationshipForm()
    {
        static $form = '';
        if (! is_object($form)) {
            $registry = Zend_Registry::getInstance();
            
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $userInfo = $registry->get("userInfo");
            
            $form = new Ml_Form_Relationship(array(
                'action' => $router->assemble(array("username" => $userInfo['alias']), "contact_relationship"),
                'method' => 'post',
            ));
            
        }
        return $form;
    }
}
