<?php
class Ml_Ignore extends Ml_Db
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
    
    protected $_name = "ignore";
    
    public function status($uid, $ignoreUid)
    {
        $select = $this->select()
        ->where("`uid` = ?", $uid)
        ->where("`ignore` = ?", $ignoreUid);
        
        $status = $this->getAdapter()->fetchRow($select);
        
        return $status;
    }
    
    public function set($uid, $ignoreUid)
    {
        $contacts = Ml_Contacts::getInstance();
        $favorites = Ml_Favorites::getInstance();
        
        $this->getAdapter()->beginTransaction();
        
        try {
            $contacts->delete($this->getAdapter()
            ->quoteInto('`uid` = ?', $uid) . $this->getAdapter()
            ->quoteInto(' AND `has` = ?', $ignoreUid));
            
            $contacts->delete($this->getAdapter()
            ->quoteInto('`uid` = ?', $ignoreUid) . $this->getAdapter()
            ->quoteInto(' AND `has` = ?', $uid));
            
            $favorites->delete($this->getAdapter()
            ->quoteInto('`uid` = ?', $ignoreUid) . $this->getAdapter()
            ->quoteInto(' AND `byUid` = ?', $uid));
            
            $favorites->delete($this->getAdapter()
            ->quoteInto('`byUid` = ?', $ignoreUid) . $this->getAdapter()
            ->quoteInto(' AND `uid` = ?', $uid));
            
            $this->insert(array("uid" => $uid, "ignore" => $ignoreUid));
            $this->getAdapter()->commit();
        } catch(Exception $e)
        {
            $contacts->getAdapter()->rollBack();
            throw $e;
        }
        
        return $this->getAdapter()->lastInsertId();
    }
    
    public function remove($uid, $ignoreUid)
    {
          return $this->delete($this->getAdapter()
          ->quoteInto('`uid` = ?', $uid) . $this->getAdapter()
          ->quoteInto(' AND `ignore` = ?', $ignoreUid));
    }
    
    public function getIgnorePage($uid, $perPage, $page, $reverse = false)
    {
        if ($reverse) {
            $uidF = 'ignore';
            $hasF = 'uid';
        } else {
            $uidF = 'uid';
            $hasF = 'ignore';
        }
        
        $select = $this->select();
        $select
        ->where($this->_name.".".$uidF." = ?", $uid)
        ->order($this->_name.".timestamp DESC");
        
        $this->joinPeopleInfo($select, $this->_name, $hasF);
        
        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($perPage);
        
        return $paginator;
    }
    
    public function form()
    {
        static $form = '';
        
        if (! is_object($form)) {
            $registry = Zend_Registry::getInstance();
            
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $userInfo = $registry->get("userInfo");
            
            $form = new Ml_Form_Ignore(array(
                'action' => $router->assemble(array("username" =>
                 $userInfo['alias']), "contactRelationshipIgnore"),
                'method' => 'post',
            ));
        }
        return $form;
    }
}