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
    protected static $_hash_quantity = 0; //number of times the magiccookie was used
    
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
    {}
    
    
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
        $hex_value = preg_replace('/[^a-f0-9]/', '', $hash);
        $memcache_safe_value = ML_MagicCookies::memcache_prefix . $hex_value;
        
        $hashInfo = $memCache->load($memcache_safe_value);
        
        return $hashInfo;
    }
    
    private static function setNewLast()
    {
        $registry = Zend_Registry::getInstance();
        $auth = Zend_Auth::getInstance();
        
        $memCache = $registry->get("memCache");
        
        $MagicCookiesNamespace = new Zend_Session_Namespace('MagicCookies');
        
        $MagicCookiesNamespace->setExpirationSeconds(($auth->hasIdentity()) ? self::authenticated_last_max_age : self::last_max_age);
        
        $new_hash = md5(mt_rand().mt_rand().mt_rand());
        
        $MagicCookiesNamespace->cached_hash = $new_hash;
        
        $store_hash = array(
            "hash" => $new_hash,
            "timestamp" => time(),
            "session_id" => Zend_Session::getId(),
               "uid" => ($auth->hasIdentity()) ? $auth->getIdentity() : null,
            "remote_addr" => (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : null
            );
            
            $memCache->save($store_hash, self::memcache_prefix.$new_hash, array(), ($auth->hasIdentity()) ? self::authenticated_max_age : self::max_age);
            
        return $new_hash;
    }
    
    /**
     * gets a magic cookie
     * @param bool $set_new_on_failure flag to set if a new hash should be
     * created in case there's not a valid one in the session cache
     */
    public static function getLast($set_new_on_failure = false)
    {
        $MagicCookiesNamespace = new Zend_Session_Namespace('MagicCookies');
        
        if(isset($MagicCookiesNamespace->cached_hash)) {
            return $MagicCookiesNamespace->cached_hash;
        }
        
        return ($set_new_on_failure) ? self::setNewLast() : false;
    }
    
    public static function formElement()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        
        $registry = Zend_Registry::getInstance();
        
        $config = $registry->get("config");
        
        $hidden = new Zend_Form_Element_Hidden(self::hash_name, array("required" => true,
            'filters'    => array('MagicCookies'),
            'validators' => array(
                array('validator' => 'MagicCookies', 'options' => array("allowed_referer_hosts" => array($config['webhost'])))
                )));
        
        $hidden->clearDecorators();
        
        //see bug http://framework.zend.com/issues/browse/ZF-8449
        $hidden->setAttrib("id", "hash".self::$_hash_quantity);
        
        self::$_hash_quantity += 1;
        
        $hidden->setValue(self::getLast(true));
        
        $hidden->setDecorators(array(
            'ViewHelper',
            'Errors',
            array(
                'HtmlTag', array(
                'tag' => 'dd')
                )
            )
        );
        
        return $hidden;
    }
}