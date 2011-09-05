<?php
class ML_People extends ML_getModel
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
    
    protected $_name = "people";
    
    public function getByUsername($username)
    {
        $select = $this->select()
        ->where("binary `alias` = ?", $username)
        ;
        return $this->getAdapter()->fetchRow($select);
    }
    
    public function getByEmail($email)
    {
        $select = $this->select()
        ->where("binary `email` = ?", $email)
        ;
        
        return $this->getAdapter()->fetchRow($select);
    }
    
    public function getById($uid)
    {
        $data = parent::getById($uid);
        
        return $data;
    }
}
