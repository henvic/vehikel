<?php
class Ml_Model_EmailChange extends Ml_Model_Db_Table
{
    protected $_name = "emailChange";
    protected $_primary = "uid";
    
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
    
    public function askNew($uid, $email, $name)
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get('config');
        
        $securitycode =
         sha1($uid . $email . md5(time() . microtime()) .
          deg2rad(mt_rand(0, 360)));
        
        $this->getAdapter()->query('INSERT INTO `emailChange` (`uid`, `email`, `securitycode`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE email=VALUES(email), securitycode=VALUES(securitycode)',
        array($uid, $email, $securitycode));
        
        return $securitycode;
    }
}