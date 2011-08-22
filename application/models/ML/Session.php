<?php
class ML_Session extends ML_getModel
{
	const OPEN_STATUS = "open";
	const CLOSE_STATUS = "close";
	const CLOSE_REMOTE_STATUS = "close_remote";
	const CLOSE_GC_STATUS = "close_gc";
	const RECENT_ACCESS_INTERVAL = "2 DAY";
	const RECENT_ACCESS_SECONDS = "172800";
	
	protected $cache = '';
	
	protected $_sessionPrefix;
	
	protected $_lastActivityPrefix;
	
	/**
     * Singleton instance
     *
     * @var Zend_Auth
     */
    protected static $_instance = null;
    
	protected $_name = "user_sessions_lookup";
	
	/**
     * Singleton pattern implementation makes "new" unavailable
     *
     * @return void
     */
	public final function __construct($config = array())
    {
    	$registry = Zend_Registry::getInstance();
    	
    	$handler = $registry->get("memCache");
    	
    	$this->_cache = $handler;
    	
    	$session_handler = Zend_Session::getSaveHandler();
    	
    	$this->_sessionPrefix = $session_handler->getSessionPrefix();
    	
    	$this->_lastActivityPrefix = $session_handler->getlastActivityPrefix();
    	
    	parent::__construct($config);
    }
	
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
	
	/**
     * Retrieve session prefix
     *
     * @return string
     */
    protected function getSessionPrefix()
    {
        return $this->_sessionPrefix;
    }
    
	/**
     * Retrieve activity prefix
     *
     * @return string
     */
    public function getlastActivityPrefix()
    {
        return $this->_lastActivityPrefix;
    }
    
	/**
	 * Meta session garbage collector
	 * 
	 * @param big int $uid
	 * 
	 * @return array of closed old sessions
	 */
	public function meta_gc($uid)
	{
		$list = $this->listRecentSessionsMeta($uid, true);
		
		$close = array();
		
		foreach($list as $key => $one_session)
		{
			if($one_session['status'] != self::OPEN_STATUS)
			{
				continue;
			}
			
			$test_session = $this->_cache->test($this->getSessionPrefix() . $one_session['session']);
			
			if(!$test_session)
			{
				$stmt = 'UPDATE '.$this->getAdapter()->quoteTableAs($this->_name).' SET `status` = ?, `end` = CURRENT_TIMESTAMP WHERE `uid` = ? AND `session` = ?';
				$this->getAdapter()->query($stmt, array(self::CLOSE_GC_STATUS, $uid, $one_session['session']));
				
				$close[] = $one_session['session'];
			}
		}
		
		return $close;
	}
	
	public function removeSessions($list, $exception = null)
	{
		foreach($list as $one_session)
		{
			if($one_session['session'] != $exception)
			{
				$this->_cache->remove($this->_sessionPrefix . $one_session['session']);
			}
		}
	}
	
	public function removeAllSessions($uid)
	{
		$sessions_list = $this->listRecentSessionsMeta($uid, true);
		
		$this->removeSessions($sessions_list);
	}
	
	public function logout()
	{
		$Log = ML_Log::getInstance();
		$Log->action("logout");
		$auth = Zend_Auth::getInstance();
		
		$old_uid = $auth->getIdentity();
		
		$auth->clearIdentity();
		
		$old_sid = Zend_Session::getId();
		
   		Zend_Session::regenerateId();
   		Zend_Session::destroy(true);
		Zend_Session::expireSessionCookie();
		$stmt = 'UPDATE '.$this->getAdapter()->quoteTableAs($this->_name).' SET `status` = ?, `end` = CURRENT_TIMESTAMP, `end_remote_addr` = ? WHERE `session` = ?';
		$this->getAdapter()->query($stmt, array(self::CLOSE_STATUS, ($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null, $old_sid));
	}
	
	public function remoteLogout()
	{
		$auth = Zend_Auth::getInstance();
		
		$Log = ML_Log::getInstance();
		$Log->action("remote_logout");
		
		$sessions_list = $this->listRecentSessionsMeta($auth->getIdentity());
		
		$current_sid = Zend_Session::getId();
		
		$this->removeSessions($sessions_list, $current_sid);
		
		$stmt = 'UPDATE '.
			$this->getAdapter()->quoteTableAs($this->_name).' '.
			'SET `status` = ?, `end` = CURRENT_TIMESTAMP, `end_remote_addr` = ? '.
			'WHERE `status` = ? AND `uid` = ? AND `session` != ?'
			;
		
		$this->getAdapter()->query(
			$stmt, array(
				self::CLOSE_REMOTE_STATUS,
				($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
				self::OPEN_STATUS,
				$auth->getIdentity(),
				$current_sid
				)
			);
	}
	
	protected function listRecentSessionsMeta($uid, $only_open = false)
	{
		$sql = "SELECT * FROM ".$this->getAdapter()->quoteTableAs($this->_name)." WHERE uid = ? AND (status = ?";

		if(!$only_open)
		{
			$sql.=" OR end > DATE_SUB(NOW(), INTERVAL ".self::RECENT_ACCESS_INTERVAL.")";
		}
		
		$sql.=") ORDER BY creation desc, end desc";
		
		$bind = array($uid, self::OPEN_STATUS);
		
		$query = $this->getAdapter()->query($sql, $bind);
		
		$result = $query->fetchAll();
		
		return (is_array($result)) ? $result : array();
	}
	
	/**
	 * 
	 * Gets a array with the information about the sessions the user is logged in at the time
	 * ordened from the last to the first
	 * @param big int $uid
	 */
	public function getRecentActivity($uid)
	{
		$this->meta_gc($uid);
		
		$sessions_list = $this->listRecentSessionsMeta($uid);
		
		$activity = array();
		
		foreach($sessions_list as $one_session)
		{
			$last_activity_info = $this->_cache->load($this->getSessionPrefix() . $this->_lastActivityPrefix . $one_session['session']);
			
			if(is_array($last_activity_info))
			{
				$last_activity_info['session'] = $one_session['session'];
				
				$last_activity_info['status'] = $one_session['status'];
				
				if(extension_loaded("geoip") && geoip_db_avail(GEOIP_COUNTRY_EDITION) && isset($last_activity_info['remote_addr']))
				{
					$last_activity_info['geo'] = @geoip_record_by_name($last_activity_info['remote_addr']);
				} else {
					$last_activity_info['geo'] = false;
				}
				
				if(isset($last_activity_info['request_time']) && $last_activity_info['request_time'] > ((int)$_SERVER['REQUEST_TIME'] - self::RECENT_ACCESS_SECONDS))
				{
					$activity[] = $last_activity_info;
				}
			}
		}
		
		return $activity;
	}
	
	public function associate($uid, $session_id)
	{
		$data = array(
			"uid" => $uid,
			"session" => $session_id,
			"status" => self::OPEN_STATUS,
			"creation_remote_addr" => ($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null
		);
		
		$this->insert($data);
	}
}

