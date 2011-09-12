<?php

class Ml_Credits extends Ml_Model_Db
{
    const cents_USD = "cents_usd";
    
    const COUPON_REDEEM = "redeem";
    
    // base58 -uppercase -a-e-u (better base, avoid bad words)
    const base = "123456789bcdfghjklmnpqrstwxyz";
    
    /**
     * Singleton instance
     *
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
    
    protected $_name = "transactions";
    
    /**
     * 
     * Shows how much credit/debit a given user has
     * @param $uid
     * @return credit/debit value
     */
    public function view($uid, $sack = self::cents_USD)
    {
        $query = $this->select()
        ->from($this->_name, "sack, SUM(amount) as amount")
        ->where("binary `uid` = ?", $uid)
        ->where("sack = ?", $sack);
        
        $resp = $this->getAdapter()->fetchRow($query);
        
        return $resp;
    }
    
    /**
     * 
     * The debit limit for a given user
     * @param $uid
     * @return limit
     */
    public function getLimit($uid, $sack = self::cents_USD)
    {
        return 0;
    }
    
    /**
     * Makes a pseudo-uniquely ID
     * 
     * Format:
     * x-y-z
     * 
     * whereas:
     * x - time portion
     * y - random number
     * z - check digit
     * 
     * There's a collision chance that must be avoided
     * with integrity check where necessary.
     * 
     */
    public static function makeUUId()
    {
        $lowerLimit = pow(29, 3);//==2111
        $upperLimit = pow(29, 4) - 1;//==ZZZZ
        $timeDivisor = 2.5;
        $ptime = (int)((int)$_SERVER['REQUEST_TIME']/$timeDivisor);
        
        $rand1 = mt_rand($lowerLimit, $upperLimit);
        $rand2 = mt_rand($lowerLimit, $upperLimit);
        
        $num1 = base_encode($ptime, self::base);
        $num2 = base_encode($rand1, self::base);
        $num3 = base_encode($rand2, self::base);
        
        $uuid = $num1.$num2.$num3.Ml_Model_Verhoeff::calcsum($ptime.$rand1.$rand2);
        
        return $uuid;
    }
    
    
    public static function printFormatedUUId($uuid)
    {
        $formattedUuid =
         mb_strtoupper(mb_substr($uuid, 0, - 9) . '-' .
         mb_substr($uuid, - 9, - 5) . '-' .
         mb_substr($uuid, - 5, - 1) . '-' .
         mb_substr($uuid, - 1));
         
        return $formattedUuid;
    }
    
    /**
     * makes a transaction
     * @param $uid
     * @param $amount (negative: debited, positive: credited)
     * @param $sack
     * @param $overrideCreditLimit when true the transaction is made regardless of any debit status
     */
    public function transaction($uid, $amount, $sack, $type, $id, $overrideCreditLimit = false)
    {
        $log = Ml_Model_Log::getInstance();
        if (! is_int($amount)) {
            throw new Exception("Transaction's amount must be a integer");
        }
        
        $this->getAdapter()->beginTransaction();
        if ($overrideCreditLimit) {
            $this->insert(array("pid" => $this->makeUUId(), "uid" => $uid, "amount" => $amount, "sack" => $sack, "reason_type" => $type, "reason_id" => $id));
        } else {
            $this->getAdapter()->query("
INSERT INTO transactions(`pid`, `uid`, `amount`, `sack`, `reason_type`, `reason_id`) 
    SELECT ?, ?, ?, ?, ?, ?
        FROM dual
        WHERE (SELECT IF((SELECT (? + sum(amount)) FROM transactions
                             WHERE uid = ?) >= ? , 1, 0))
            ", array());
        }
        
        //IF (SELECT SUM(amount) FROM ? WHERE uid = ? AND sack = ?) >= ?
        
        
        
        $transactionId = $this->getAdapter()->lastInsertId();
        $log->action("transaction", $transactionId);
        $this->getAdapter()->commit();
        return $transactionId;
    }
    
    /**
     * makes a coupon's based transaction
     * @param big int $uid
     * @param faken hexdec $coupon
     */
    public function couponTransaction($uid, $coupon)
    {
        $coupons = Ml_Model_Coupons::getInstance();
        $log = Ml_Model_Log::getInstance();
        
        $this->getAdapter()->beginTransaction();
        $select = $coupons->select();
        $select->where("hash = ?", $coupon)->where("active = ?", true);
        
        $row = $coupons->fetchRow($select);
        if (is_object($row)) {
            $couponData = $row->toArray();
            
            if ($couponData['unique_use'] &&
             ! $coupons->update(array("active" => false), 
            $this->getAdapter()
                ->quoteInto("hash = ?", $couponData['hash']))) {
                throw new Exception("Error changing the active status of the coupon to false.");
            }
            if (! $couponData['unique_use']) {
                //then checks if it was already used by this user:
                //using fetchRow 'cause 1 result is enough
                $isItUsed = $this->fetchRow($this->select()
                ->where("binary `uid` = ?", $uid)
                ->where("reason_type = ?", Ml_Credits::COUPON_REDEEM)
                ->where("binary `reason_id` = ?", $couponData['id']));
                
                if (is_object($isItUsed)) {
                    $this->getAdapter()->rollBack();
                    return false;
                }
            }
            
            $this->insert(array("pid" => $this->makeUUId(), "uid" => $uid, 
            "amount" => $couponData['amount'], "sack" => $couponData['sack'], 
            "reason_type" => self::COUPON_REDEEM, 
            "reason_id" => $couponData['id']));
            
            $transactionId = $this->getAdapter()->lastInsertId();
            $log->action("transaction", $transactionId);
            
            $this->getAdapter()->commit();
            
            return $transactionId;
        }
    }
    
    public function history($uid, $perPage, $page)
    {
        $select = $this->select()
        ->where("binary `uid` = ?", $uid)
        ->order("timestamp DESC");
        
        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($perPage);
        
        return $paginator;
    }
    
    public function getByPId($id)
    {
        $select = $this->select()
        ->where("binary `pid` = ?", $id);
        
        return $this->getAdapter()->fetchRow($select);
    }
}