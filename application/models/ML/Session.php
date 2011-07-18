<?php
class ML_Session extends ML_getModel
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
	
	protected $_name = "session";
	
	public function remoteLogout()
	{
		$Log = ML_Log::getInstance();
		$Log->action("remote_logout");
		$this->delete($this->getAdapter()->quoteInto('uid = ?', Zend_Auth::getInstance()->getIdentity()).$this->getAdapter()->quoteInto(' AND id != ?', Zend_Session::getId()));
	}
	
	public function logout()
	{
		$Log = ML_Log::getInstance();
		$Log->action("logout");
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();
   		Zend_Session::regenerateId();
   		Zend_Session::destroy(true);
		Zend_Session::expireSessionCookie();
	}
	
	/**
	 * 
	 * Gets a array with the information about the sessions the user is logged in at the time
	 * ordened from the last to the first
	 * @param big int $uid
	 * @param $limit
	 */
	public function getByUid($uid, $limit = 0)
	{
		$select = $this->select()->where("uid = ?", $uid)
		->order("modified DESC")->limit($limit)
		;
		
		$result = $this->fetchAll($select);
		
		$data = $result->toArray();
		
		if(!empty($data))
		{
			$activity = array();
			
			foreach($data as $datum)
			{
				$activity[] = array("type" => "browser", "last_access_timestamp" => $datum['modified']);
			}
		}
		
		return $activity;
	}
}

