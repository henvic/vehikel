<?php
/**
 * Magic cookies is a class for creating cookies
 * like the magic_cookies (or global_auth_hash) on Flickr.
 * It's a piece of data that only the legitimate
 * user and the server should have access to.
 * 
 * So it can be used to check requests that uses it, like deleting
 * or adding some data. If it is not there, the request should
 * not be considered.
 * 
 * We only clear the older hashs in check(), we don't call it anywhere else.
 * We don't need to check everytime the user does something, it's a waste of power.
 * 
 * @author henrique
 *
 * @todo check referrer header
 * @todo unit testing... There is something wrong that could be avoided if I did
 * it in first place... But I didn't :(
 */
class ML_MagicCookies extends ML_getModel
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
	
	const MSG_INVALID_HTTP_REFERER = 'refererInvalid';
	
	protected $_name = "globalhash";
    
	private function set($hashtable)
	{
   		$this->getAdapter()->query
        ('INSERT INTO `'.$this->_name.'` (`uid`, `hashtable`) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE hashtable=VALUES(hashtable)',
        array(Zend_Auth::getInstance()->getIdentity(), serialize($hashtable)));
	}
	
	/**
	 * Gets a global hash.
	 * 
	 * @param $maxAge is the maximum age of the
	 * hash to get. If they are all older than it,
	 * we create a new one
	 * @return unknown_type
	 */
    public static function get($maxAge = 864000)
    {
    	$auth = Zend_Auth::getInstance();
    	$MagicCookies = self::getInstance();
    	if(!$auth->hasIdentity()) {
    		return false;
    	}
    	
    	//get the secrets in the db
    	$select = $MagicCookies->select()
		->where("`uid` = ?", $auth->getIdentity())
		;
    	$check = $MagicCookies->getAdapter()->fetchRow($select);
    	
    	if(isset($check['hashtable']))
	    	$secrets = unserialize($check['hashtable']);
    	
    	if(!empty($secrets))
    	{
	    	//now we read the newer
    		$key = key($secrets);
    		
	    	foreach($secrets as $conception => $secret)
	    	{
    			if($conception > time() - $maxAge)
    			{
    				$use = $secret; break;
    			}
    		}
    	}
    	
    	// If no secret was available... Let's create it.
    	if(!isset($use))
    	{
    		$AntiAttack = new ML_AntiAttack();
    		$use = $AntiAttack->randomSHA1();
    		$now = time();
    		$secrets[$now] = $use;
    		
    		$MagicCookies->set($secrets);
    	}
    	
    	Zend_Registry::getInstance()->set("globalHash", $use);
    	
    	return $use;
    }
    
    /**
     * Check if a given hash is valid
     * @param $maxAge is max age of the hash in seconds (default is 20 days)
     * @param $hash
     * @return age in success and false in failure
     */
    public static function check($maxAge = 1728000, $hash = '')
    {
    	$registry = Zend_Registry::getInstance();
    	$auth = Zend_Auth::getInstance();
    	
    	$config = $registry->get("config");
    	
    	if(!$auth->hasIdentity()) {
    		throw new Exception("Magic cookies: user not auth'ed.");
    	}
    	
    	if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']!='')
    	{
			$referer = Zend_Uri::factory($_SERVER['HTTP_REFERER']);
			
			if($_SERVER['REQUEST_METHOD'] == 'GET')
			switch($referer->getHost())
			{
				case $config->webhost:
				//used hosts to develop...
				case "localhost" :
				case "mercury" :
				case "127.0.0.1" :
				case "127.0.0.2" : 
				break;
				default: throw new Exception("HTTP referer invalid. Don't let it happen.");
			}
    	}
    	
    	$MagicCookies = self::getInstance();
    	$request = Zend_Controller_Front::getInstance()->getRequest();
    	
    	//get the secrets in the db
    	$select = $MagicCookies->select()
		->where("`uid` = ?", $auth->getIdentity())
		;
    	$check = $MagicCookies->getAdapter()->fetchRow($select);
    	
    	if(!isset($check['hashtable'])) {
    		throw new Exception("No hash in hashtable.");;
    	}
    	
	    $secretsInit = unserialize($check['hashtable']);
	    $secrets = $secretsInit;
	    if(!is_array($secrets)) {
	    	throw new Exception("0 hashs in hashtable.");
	    }
	    
	    /*
   		// delete older global hashes from the list of accepted for when hash != ''
   		while(key($secrets) < time() - 30*86400) {
   			// can't use array_shift because the key time() is numerical
   			$secrets = array_slice($secrets, 1, null, true);
   			//next(); if not working change to it... there's a bug? test it later
   			each($secrets);
   		}*/
   		
   		//just set again if anything changed
   		if($secrets != $secretsInit) $MagicCookies->set($secrets);
   		
    	if(empty($hash))
    	{
	    	if(isset($_POST['hash'])) {
	    		$hash = $_POST['hash'];
	    	} elseif($request->getParam("hash")) {
	    		$hash = $request->getParam("hash");
	    	} else {
	    		throw new Exception("Needed magic cookie not passed.");
	    	}
    	}
    	
    	foreach($secrets as $conception => $secret)
	    {
    		if($hash == $secret && $conception > time() - $maxAge)
    		{
    			return $conception;
    		}
    	}
    	
    	throw new Exception("Hash not found in hashtable.");
    }
    
    protected static $_hashnum = 0;
	public static function formElement()
	{
		$request = Zend_Controller_Front::getInstance()->getRequest();
		
		$registry = Zend_Registry::getInstance();
		
		if($request->isPost())//@todo put the $time inside and
		//and use formElement($time), only accept by _POST, ok? do something like this...
		{
			self::check();
		}
		/*if (null === self::$_instance) {
            self::$_instance = new self();
        }*/
		
		$hidden = new Zend_Form_Element_Hidden('hash', array("required" => true));
		
		$hidden->clearDecorators();
		
		$hidden->setAttrib("id", "hash".self::$_hashnum);
		self::$_hashnum = self::$_hashnum + 1;
		
		//see bug http://framework.zend.com/issues/browse/ZF-8449
		
		$hidden->setValue($registry->get('globalHash'));
		
		$hidden->setDecorators(array(
    'ViewHelper',
    'Errors',
    array('HtmlTag', array('tag' => 'dd')),
));
	return $hidden;
	}
}