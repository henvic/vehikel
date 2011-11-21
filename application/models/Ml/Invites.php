<?php
/**
 * This model creates invites that can be used for the sign up process
 * 
 * The invites older than the days necessary for
 * creating the MAX_INVITES can be removed
 * MAX_DAYS should be MAX_INVITES by now:
 * one invite per day is being considered
 * @author henvic
 *
 */
class Ml_Model_Invites extends Ml_Model_AccessSingleton
{
    const NOT_USED = 0;
    
    const USED = 1;
    
    const MAX_INVITES = 7;
    
    const MAX_DAYS = 10;
    
    //number of days a user is considered new: don't get invites
    const NEW_USER = 5;
    
    protected static $_dbTableName = "invites";
    
    /**
     * Singleton instance
     *
     */
    protected static $_instance = null;
    
    /**
     * 
     * Checks if invites exists or can be created
     */
    public function getNumFree()
    {
        $auth = Zend_Auth::getInstance();
        
        $signedUserInfo = self::$_registry->get("signedUserInfo");
        
        $membershipdate =
        new Zend_Date($signedUserInfo['membershipdate'], Zend_Date::ISO_8601);
        
        if ($membershipdate->compareTimestamp(time() - (self::NEW_USER * 86400)) == 1) {
            return -1;
        }
        
        $date = new Zend_Date();
        
        $select = $this->_dbTable->select()
        ->where("uid = ?", $auth->getIdentity())
        ->where("used = ?", "1")
        ->where("timestamp > DATE_ADD(?, INTERVAL -" . self::MAX_DAYS . " DAY)",
        $date->get("yyyy-MM-dd HH:mm:ss"));
        
        $used = $this->_dbAdapter->fetchAll($select);
        
        $usedNum = sizeof($used);
        
        return self::MAX_INVITES - $usedNum;
    }
    
    /**
     * 
     * Get hash information
     * @param string $hash
     */
    public function get($hash)
    {
        $select = $this->_dbTable->select()
        ->where("hash = ?", mb_strtolower($hash));
        
        $row = $this->_dbTable->fetchRow($select);
        
        if (! is_object($row)) {
            return false;
        }
        
        return $row->toArray();
    }
    
    public function create($quantity, $uid)
    {
        $tokens = array();
        
        for ($counter = 0; $counter < $quantity; $counter ++) {
            //not beautiful
            $partialFirst = Ml_Model_Numbers::baseEncode(mt_rand(((36 * 36) + 1), ((36 * 36 * 36))), 
             "qwertyuiopasdfghjklzxcvbnm0123456789");
            $partialSecond = Ml_Model_Numbers::baseEncode(mt_rand(((31 * 31) + 1), ((31 * 31 * 31))), 
             "qwrtyuopasdghjklzcvnm123456789");
            
            $tokens[] = $partialFirst . '-' . $partialSecond;
        }
        
        $escapeUid = $this->_dbAdapter->quoteInto("?", $uid);
        
        if (! empty($tokens)) {
            $querystring =
            "INSERT IGNORE INTO `" . $this->_dbTable->getTableName() . "` (`uid`, `hash`) VALUES ";
            do {
                $line = '(' . $escapeUid . ', ' .
                 $this->_dbAdapter->quoteInto("?", current($tokens)) . ')';
                
                $querystring .= $line;
                
                next($tokens);
                
                if (current($tokens)) {
                    $querystring .= ", ";
                }
                
            } while (current($tokens));
            
            $this->_dbAdapter->query($querystring);
        }
        
        return $tokens;
    }
    
    /**
     * 
     * Get available tokens for the signed in user
     * 
     * @return array of tokens
     */
    public function getTokens()
    {
        $signedUserInfo = self::$_registry->get("signedUserInfo");
        
        $membershipdate = new Zend_Date($signedUserInfo['membershipdate'], Zend_Date::ISO_8601);
        
        $numFree = $this->getNumFree();
        if ($numFree == - 1) {
            return false;
        } else if ($numFree == 0) {
            return array();
        }
        
        $select = $this->_dbTable->select()
        ->where("uid = ?", $signedUserInfo['id'])
        ->where("used = ?", "0")
        ->order("timestamp DESC");
        
        $invites = $this->_dbAdapter->fetchAll($select);
        
        $tokens = array();
        
        $left = $numFree - sizeof($invites);
        
        if ($left) {
            $tokens = $this->create($left, $signedUserInfo['id']);
        }
        
        foreach ($invites as $token) {
            $tokens[] = $token['hash'];
        }
        
        return $tokens;
    }
    
    public function updateStatus($code, $status)
    {
        $update = $this->_dbTable->update(array("solution"
        => $status), $this->_dbAdapter->quoteInto("hash = ?", $code));
        
        if ($update) {
            return true;
        } else {
            return false;
        }
    }
}