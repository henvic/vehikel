<?php

class ML_Log extends ML_getModel
{
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
    
	protected $_name = "log";
	
	
	/**
	 * 
	 * Log the client/server details
	 * in the moment of a given important action.
	 * 
	 * Kind of actions:
	 * @todo log in, log out, remote log out, transactions
	 * 
	 * Kind of information logged:
	 * sessions, IP address, browser, etc.
	 * 
	 * @param $reason_type
	 * @param $reason_id
	 */
	public function action($reason_type, $reason_id = null, $notes = null)
	{
		$registry = Zend_Registry::getInstance();
		
		$data = array();
		
		if(isset($_SERVER['REMOTE_ADDR']))
		{
			$data["remote_addr"] = $_SERVER['REMOTE_ADDR'];
		}
		
		if(isset($_SERVER['HTTP_COOKIE']))
		{
			$data['cookies'] = $_SERVER['HTTP_COOKIE'];
		}
		
		if($registry->isRegistered("signedUserInfo"))
		{
			$signedUserInfo = $registry->get("signedUserInfo");
			$data['uid'] = $signedUserInfo['id'];
		}
		
		if($notes)
		{
			$data['notes'] = $notes;
		}
		
		$data['reason_type'] = $reason_type;
		$data['reason_id'] = $reason_id;
		$data['dump'] = var_export($_SERVER, true);
		
		$this->insert($data);
	}
}