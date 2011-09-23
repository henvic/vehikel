<?php
class Ml_Model_EmailChange extends Ml_Model_AccessSingleton
{
    protected static $_dbTableName = "email_change";
    protected static $_dbPrimaryRow = "uid";
    
    /**
     * Singleton instance
     *
     */
    protected static $_instance = null;
    
    public function newChange($uid, $email, $name)
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get('config');
        
        $securityCode =
         sha1($uid . $email . md5(time() . microtime()) .
          deg2rad(mt_rand(0, 360)));
        
        $this->_dbAdapter->query('INSERT INTO ' . 
        $this->_dbAdapter->quoteTableAs($this->_dbTable->getTableName())
        . ' (`uid`, `email`, `securitycode`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE email=VALUES(email), securitycode=VALUES(securitycode)',
        array($uid, $email, $securityCode));
        
        return $securityCode;
    }
    
    public function get($uid, $securityCode)
    {
        $select = $this->_dbTable->select()->where("uid = ?", $uid)
        ->where("securitycode = ?", $securityCode)
        ->where("CURRENT_TIMESTAMP < TIMESTAMP(timestamp, '12:00:00')");
        $query = $this->_dbTable->fetchRow($select);
        
        if (! is_object($query)) {
            return false;
        }
        
        return $query->toArray();
    }
    
    /**
     * 
     * Change user's e-mail
     * @param big int $uid
     * @param string $email
     * @param bool $removeTicket removes update request ticket
     */
    public function setChange($uid, $email, $removeTicket = true)
    {
        $people = Ml_Model_People::getInstance();
        
        if ($removeTicket) {
            $rename = $people->update($uid, array("email" => $email));
            
            if (! $rename) {
                return false;
            }
        }
        
        $deleteRequest = $this->_dbTable->delete($this->_dbAdapter
        ->quoteInto('uid = ?', $uid));
        
        if (! $deleteRequest) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 
     * Garbage collector
     * @param int $maxAge in seconds
     * @return number of deleted items
     */
    public function gc($maxAge)
    {
        $deleted = $this->_dbAdapter
         ->delete($this->_dbTable->getTableName(), $this->_dbAdapter
         ->quoteInto("timestamp < ?", date("Y-m-d H:i:s", time() - ($maxAge))));
         
         return $deleted;
    }
}