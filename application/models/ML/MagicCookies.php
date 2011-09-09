<?php
/**
 * Magic cookies is a class for creating cookies
 * like the magic_cookies (or global_auth_hash) on Flickr.
 * It's a piece of data that only the legitimate
 * user and the server should have access to.
 * 
 * @author henrique
 *
 */
class ML_MagicCookies
{
    //number of times the magic cookie was used
    protected static $_hashQuantity = 0;
    
    /**
     * Singleton instance
     *
     * @var Zend_Auth
     */
    protected static $_instance = null;
    
    //all in seconds
    const last_max_age = "43200";
    const max_age = "86400";
    const authenticated_last_max_age = "432000";
    const authenticated_max_age = "864000";
    const memcache_prefix = "hash_";
    const lenght = "32";//md5 => 32 characters
    const hash_name = "hash";
    
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
    
    public static function getHashInfo($hash)
    {
        $registry = Zend_Registry::getInstance();
        
        $memCache = $registry->get("memCache");
        
        //sanitizing the key for memcache
        $hexValue = preg_replace('/[^a-f0-9]/', '', $hash);
        $memcacheSafeValue = ML_MagicCookies::memcache_prefix . $hexValue;
        
        $hashInfo = $memCache->load($memcacheSafeValue);
        
        return $hashInfo;
    }
    
    private static function setNewLast()
    {
        $registry = Zend_Registry::getInstance();
        $auth = Zend_Auth::getInstance();
        
        $memCache = $registry->get("memCache");
        
        $magicCookiesNamespace = new Zend_Session_Namespace('MagicCookies');
        
        if ($auth->hasIdentity()) {
            $expiration = self::authenticated_last_max_age;
            $maxAge = self::authenticated_max_age;
        } else {
            $expiration = self::last_max_age;
            $maxAge = self::max_age;
        }
        
        $magicCookiesNamespace->setExpirationSeconds($expiration);
        
        $newHash = md5(mt_rand().mt_rand().mt_rand());
        
        $magicCookiesNamespace->cachedHash = $newHash;
        
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $remoteAddr = $_SERVER['REMOTE_ADDR'];
        } else {
            $remoteAddr = null;
        }
        
        $storeHash = array(
            "hash" => $newHash,
            "timestamp" => time(),
            "session_id" => Zend_Session::getId(),
            "uid" => ($auth->hasIdentity()) ? $auth->getIdentity() : null,
            "remote_addr" => $remoteAddr);
            
            $memCache->save($storeHash,
             self::memcache_prefix . $newHash,
             array(),
             $maxAge);
            
        return $newHash;
    }
    
    /**
     * gets a magic cookie
     * @param bool $setNewOnFailure flag to set if a new hash should be
     * created in case there's not a valid one in the session cache
     */
    public static function getLast($setNewOnFailure = false)
    {
        $magicCookiesNamespace = new Zend_Session_Namespace('MagicCookies');
        
        if (isset($magicCookiesNamespace->cachedHash)) {
            return $magicCookiesNamespace->cachedHash;
        }
        
        return ($setNewOnFailure) ? self::setNewLast() : false;
    }
    
    public static function formElement()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        
        $registry = Zend_Registry::getInstance();
        
        $config = $registry->get("config");
        
        $hidden = new Zend_Form_Element_Hidden(self::hash_name,
         array("required" => true,
             'filters'    => array('MagicCookies'),
             'validators' => array(
               array('validator' => 'MagicCookies', 'options' =>
                array("allowed_referer_hosts" => array($config['webhost'])))
                 )));
        
        $hidden->clearDecorators();
        
        //see bug http://framework.zend.com/issues/browse/ZF-8449
        $hidden->setAttrib("id", "hash".self::$_hashQuantity);
        
        self::$_hashQuantity += 1;
        
        $hidden->setValue(self::getLast(true));
        
        $hidden->setDecorators(array(
            'ViewHelper',
            'Errors',
            array(
                'HtmlTag', array(
                'tag' => 'dd')
                )
            ));
        
        return $hidden;
    }
}