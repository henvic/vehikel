<?php
/*
 * The invites older than the days necessary for
 * creating the max_invites can be removed
 * max_days should be max_invites by now:
 * one invite per day is being considered
 */
class ML_Invites extends ML_getModel
{
    protected $_name = "invites";
    
    const max_invites = 7;
    
    const max_days = 10;
    
    //number of days a user is considered new: don't get invites
    const new_user = 5;
    
    /*
     * Checks if invites exists or can be created
     */
    public function numfree()
    {
        $auth = Zend_Auth::getInstance();
        $registry = Zend_Registry::getInstance();
        
        $signedUserInfo = $registry->get("signedUserInfo");
        
        $membershipdate =
         new Zend_Date($signedUserInfo['membershipdate'], Zend_Date::ISO_8601);
        
        if ($membershipdate->compareTimestamp(time() - (self::new_user * 86400)) == 1) {
            return -1;
        }
        
        $date = new Zend_Date();
        
        $select = $this->select()
        ->where("uid = ?", $auth->getIdentity())
        ->where("used = ?", "1")
        ->where("timestamp > DATE_ADD(?, INTERVAL -".self::max_days." DAY)", $date->get("yyyy-MM-dd HH:mm:ss"));
        
        $used = $this->fetchAll($select)->toArray();
        
        $usedNum = sizeof($used);
        
        return self::max_invites - $usedNum;
    }
    
    public function create($quantity, $uid)
    {
        $tokens = array();
        
        for ($counter = 0; $counter < $quantity; $counter ++) {
            //not beautiful
            $partialFirst = base_encode(mt_rand(((36 * 36) + 1), ((36 * 36 * 36))), 
             "qwertyuiopasdfghjklzxcvbnm0123456789");
            $partialSecond = base_encode(mt_rand(((31 * 31) + 1), ((31 * 31 * 31))), 
             "qwrtyuopasdghjklzcvnm123456789");
            
            $tokens[] = $partialFirst . '-' . $partialSecond;
        }
        
        $escapeUid = $this->getAdapter()->quoteInto("?", $uid);
        
        if (! empty($tokens)) {
            $querystring = "INSERT IGNORE INTO `" . $this->getTableName() . "` (`uid`, `hash`) VALUES ";
            do {
                $line = '(' . $escapeUid . ', ' .
                 $this->getAdapter()->quoteInto("?", current($tokens)) . ')';
                
                $querystring .= $line;
                
                next($tokens);
                
                if(current($tokens)) $querystring.=", ";
                
            } while (current($tokens));
            
            $this->getAdapter()->query($querystring);
        }
        
        return $tokens;
    }
    
    public function get()
    {
        $registry = Zend_Registry::getInstance();
        
        $signedUserInfo = $registry->get("signedUserInfo");
        
        $membershipdate = new Zend_Date($signedUserInfo['membershipdate'], Zend_Date::ISO_8601);
        
        $numfree = $this->numfree();
        if ($numfree == - 1) {
            return false;
        } else if ($numfree == 0) {
            return array();
        }
        
        $select = $this->select()
        ->where("uid = ?", $signedUserInfo['id'])
        ->where("used = ?", "0")
        ->order("timestamp DESC");
        
        $invites = $this->fetchAll($select)->toArray();
        
        $tokens = array();
        
        $left = $numfree - sizeof($invites);
        
        if ($left) {
            $tokens = $this->create($left, $signedUserInfo['id']);
        }
        
        foreach ($invites as $token) {
            $tokens[] = $token['hash'];
        }
        
        return $tokens;
    }
}