<?php
class Ml_Model_Recover extends Ml_Model_AccessSingleton
{
    protected static $_dbTableName = "recover";
    
    protected static $_dbPrimaryRow = "uid";
    
    /**
     * Singleton instance
     *
     */
    protected static $_instance = null;
    
    public static function form()
    {
        static $form = '';
        
        if (! is_object($form)) {
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $form = new Ml_Form_Recover(array(
                'action' => $router->assemble(array(), "recover"),
                'method' => 'post',
            ));
        }
        return $form;
    }
    
    /**
     * 
     * Creates a new case for password recovery
     * @param big int $uid
     * @return security code
     */
    public function newCase($uid)
    {
        $securityCode = sha1(md5(mt_rand(0, 1000) . time() . microtime()) .
        deg2rad(mt_rand(0, 360)));
        
        $this->_dbAdapter
        ->query('INSERT INTO `recover` (`uid`, `securitycode`) VALUES (?, ?) ON DUPLICATE KEY UPDATE uid=VALUES(uid), securitycode=VALUES(securitycode), timestamp=CURRENT_TIMESTAMP',
        array($uid, $securityCode));
        
        return $securityCode;
    }
    
    /**
     * 
     * Remove the existing case for password recovery for a given user
     * @param big int $uid
     */
    public function closeCase($uid)
    {
        $delete = $this->_dbTable->delete($this->_dbTable->quoteInto('uid = ?', $uid));
        
        return ($delete) ? true : false;
    }
    
    /**
     * 
     * Get authorization for password change / account recovery
     * @param big int $uid
     * @param string $securityCode
     * @return authorization info array on success, false on failure
     */
    public function getAuthorization($uid, $securityCode)
    {
        $select = $this->_dbTable->select()
        ->where("uid = ?", $uid)
        ->where("securitycode = ?", $securityCode)
        ->where("CURRENT_TIMESTAMP < TIMESTAMP(timestamp, '12:00:00')");
        
        $recoverInfo = $this->_dbTable->fetchRow($select);
        if (! is_object($recoverInfo)) {
            return false;
        }
        return $recoverInfo->toArray();
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
