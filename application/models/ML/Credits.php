<?php

class ML_Credits extends ML_getModel
{
	const cents_USD = "cents_usd";
	
	const COUPON_REDEEM = "redeem";
	
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
    {}
    
    
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
		->where("sack = ?", $sack)
		;
		
		$resp = $this->getAdapter()->fetchRow($query);
		
		return $resp;
    }
    
	/**
     * 
     * Shows the limit of debit for a given user (so that he/she still can make new payments)
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
		//$base = "123456789ABCDEFGHJKLMNPQRSTUVWXYZ";
		//fuck and ass appearing is possible, so let's try another base :(
		//$upper_limit = 1291467968;//1291467968 == ZZZZZZ for this ^
		
		$base = "123456789bcdfghjklmnpqrstuwxyz";// base58 -uppercase -a-e-u (better base, avoid bad words)
		$lower_limit = 27000;//==2111
		$upper_limit = 809999;//==ZZZZ
		$time_divisor = 2.5;
		$ptime = (int)($_SERVER['REQUEST_TIME']/$time_divisor);
		
		$rand1 = mt_rand($lower_limit, $upper_limit);
		$rand2 = mt_rand($lower_limit, $upper_limit);
		
		
		$num1 = base_encode($ptime, $base);
		$num2 = base_encode($rand1, $base);
		$num3 = base_encode($rand2, $base);
		
		$uuid = $num1.$num2.$num3.ML_Verhoeff::calcsum($ptime.$rand1.$rand2);
		
		return $uuid;
    }
    
    
    public static function printFormatedUUId($uuid)
    {
    	$formatted_uuid = mb_strtoupper(mb_substr($uuid, 0, -9).'-'.mb_substr($uuid, -9, -5).'-'.mb_substr($uuid, -5, -1).'-'.mb_substr($uuid, -1));
    	return $formatted_uuid;
    }
    
	/**
     * makes a transaction
     * @param $uid
     * @param $amount (negative: debited, positive: credited)
     * @param $sack
     * @param $override_credit_limit when true the transaction will be made regardless of any incurring debit
     */
    public function transaction($uid, $amount, $sack, $type, $id, $override_credit_limit = false)
    {
    	$Log = ML_Log::getInstance();
    	if(!is_int($amount))
    	{
    		throw new Exception("Transaction's amount must be a integer");
    	}
    	
    	$this->getAdapter()->beginTransaction();
    	if($override_credit_limit)
    	{
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
    	
    	
    	
    	$transaction_id = $this->getAdapter()->lastInsertId();
    	$Log->action("transaction", $transaction_id);
    	$this->getAdapter()->commit();
    	return $transaction_id;
    }
    
    /**
     * makes a coupon's based transaction
     * @param big int $uid
     * @param faken hexdec $coupon
     */
    public function couponTransaction($uid, $coupon)
    {
    	$Coupons = ML_Coupons::getInstance();
    	$Log = ML_Log::getInstance();
    	
    	$this->getAdapter()->beginTransaction();
    	$select = $Coupons->select();
		$select->where("hash = ?", $coupon)->where("active = ?", true)
		;
		
		$row = $Coupons->fetchRow($select);
		if(is_object($row))
		{
			$coupon_data = $row->toArray();
			
			if($coupon_data['unique_use'] && !$Coupons->update(array("active" => false), $this->getAdapter()->quoteInto("hash = ?", $coupon_data['hash'])))
			{
				throw new Exception("Error changing the active status of the coupon to false.");
			}
			
			if(!$coupon_data['unique_use'])
			{
				//then checks if it was already used by this user: using fetchRow 'cause 1 result is enough
				$is_it_used = $this->fetchRow($this->select()
				->where("binary `uid` = ?", $uid)
				->where("reason_type = ?", ML_Credits::COUPON_REDEEM)
				->where("binary `reason_id` = ?", $coupon_data['id']));
				
				if(is_object($is_it_used))
				{
					$this->getAdapter()->rollBack();
					return false;
				}
			}
			
			$this->insert(array("pid" => $this->makeUUId(), "uid" => $uid, "amount" => $coupon_data['amount'], "sack" => $coupon_data['sack'], "reason_type" => self::COUPON_REDEEM, "reason_id" => $coupon_data['id']));
			$transaction_id = $this->getAdapter()->lastInsertId();
			$Log->action("transaction", $transaction_id);
			
			$this->getAdapter()->commit();
			
			return $transaction_id;
		}
    }
	
	public function history($uid, $per_page, $page)
	{
		$select = $this->select()
		->where("binary `uid` = ?", $uid)
		->order("timestamp DESC")
		;
		
		$paginator = Zend_Paginator::factory($select);
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage($per_page);
		
		return $paginator;
	}
	
	public function getByPId($id)
	{
		$select = $this->select()
		->where("binary `pid` = ?", $id)
		;
		
		return $this->getAdapter()->fetchRow($select);
	}
}