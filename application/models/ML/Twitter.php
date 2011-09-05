<?php
class ML_Twitter extends ML_getModel
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
    
    protected $_name = "twitter";
    protected $_primary = "uid";
    
    public function form()
    {
        static $form = '';
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        if(!is_object($form))
        {
            $registry = Zend_Registry::getInstance();
            $shareInfo = $registry->get('shareInfo');
            $userInfo = $registry->get('userInfo');
             
            require_once APPLICATION_PATH . '/forms/Tweet.php';
             
            $form = new Form_Tweet(array(
                'action' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array("username" => $userInfo['alias'], "share_id" => $shareInfo['id']), "sharepage_1stpage") .'?tweet',
                'method' => 'post',
            ));
        }
        $form->setDefault("hash", $registry->get('globalHash'));
        
        $form->setDefault("tweet", $shareInfo['title'] . ' ' . $config['URLshortening']['twitterlink'].base58_encode($shareInfo['id']));
        
        return $form;
    }
    
    
    public function tweet($msg)
    {
        $auth = Zend_Auth::getInstance();
        $registry = Zend_Registry::getInstance();
        
        $config = $registry->get('config');
        
        $twitterConf = $config['services']['twitter'];
        
        if(!$twitterConf['available']) {
            return array("error" => "unavailable");
        }
        
        $twitterAccount = $this->getSignedUserTwitterAccount();
        
        if(mb_strlen($msg) > 140) {
            return array("error" => "msg_too_long");
        }
        
        if(!is_array($twitterAccount)) {
            return array("error" => "account_not_found");
        }
        
        $twitterObj = new EpiTwitter($twitterConf['key'], $twitterConf['secret'], $twitterAccount['oauth_token'], $twitterAccount['oauth_token_secret']);
        
        $twitterInfo = new Zend_Session_Namespace('twitterInfo');
        $recent_history = $twitterInfo->tweets_recent_history;
        
        if(is_array($recent_history)) {
            $delete_timer = array();
            foreach($recent_history as $key => $oneTime) {
                if($oneTime < time()-700) {
                    $delete_timer[] = $key;
                }
            }
            if(!empty($delete_timer))
            {
                foreach($delete_timer as $key_value)
                {
                    unset($recent_history[$key_value]);
                }
            }
            
            
        }
        
        if(10 < sizeof($recent_history)) {
            return array("error" => "too_many_tweets");
        }
        
        $update_status = $twitterObj->post_statusesUpdate(array('status' => $msg));
        
        $recent_history[] = time();
        
        $twitterInfo->unlock();
        $twitterInfo->tweets_recent_history = $recent_history;
        $twitterInfo->setExpirationSeconds(3600, "tweets_recent_history");
        $twitterInfo->lock();
        
        $response = json_decode(($update_status->responseText), true);
        return (!is_array($response)) ? false : $response;//response maybe a response['error']
    }
    
    public function getSignedUserTwitterAccount()
    {
        $auth = Zend_Auth::getInstance();
        
        if(!$auth->hasIdentity()) throw new Exception("Not signed in.");
        
        $twitterInfo = new Zend_Session_Namespace('twitterInfo');
        
        if(!$twitterInfo->isLocked())
        {
            $select = $this->select()->where("uid = ?", $auth->getIdentity());
            $row = $this->fetchRow($select);
            
            $twitterInfo->account = (is_object($row)) ? $row->toArray() : false;
            
            $twitterInfo->setExpirationSeconds(86400);//we like fresh data
            
            $twitterInfo->lock();
        }
        
        return $twitterInfo->account;
    }
    
    public function setTwitterAccount($token, $twitterInfo = false)
    {
        $registry = Zend_Registry::getInstance();
        $auth = Zend_Auth::getInstance();
        
        $config = $registry->get('config');
        
        $twitterConf = $config['services']['twitter'];
        
        $twitterObj = new EpiTwitter($twitterConf['key'], $twitterConf['secret']);
        
        $twitterInfo_namespace = new Zend_Session_Namespace('twitterInfo');
        $twitterInfo_namespace->unlock();//so next time we want to read it, it fetchs
        //the information again, just in case anything changed
        
        if(!$token)
        {
            $token = array("oauth_token" => $twitterInfo['oauth_token'],
                "oauth_token_secret" => $twitterInfo['oauth_token_secret']);
        }
        $twitterObj->setToken($token['oauth_token'], $token['oauth_token_secret']);
        
        $resp = $twitterObj->get_accountVerify_credentials();
        
        $twitterResponse = json_decode($resp->responseText, true);
        
        if(!is_array($twitterResponse) || isset($twitterResponse["error"])) return false;
        
        //put the max sizes of screen_name and name for twitter somewhere else
        if(!array_key_exists("id", $twitterResponse) ||
            !array_key_exists("name", $twitterResponse) ||
            !array_key_exists("screen_name", $twitterResponse) ||
            mb_strlen($twitterResponse['screen_name']) > 15 ||
            mb_strlen($twitterResponse['name']) > 20
            ) return false;
        
        if($twitterInfo && $twitterInfo['id'] == $twitterResponse['id'] &&
            $twitterInfo['screen_name'] == $twitterResponse['screen_name'] &&
            $twitterInfo['name'] == $twitterResponse['name']) return $twitterInfo;
        
        $this->getAdapter()->query
                    ('INSERT INTO '.$this->getAdapter()->quoteTableAs($this->_name).' (`id`, `uid`, `oauth_token`, `oauth_token_secret`, `screen_name`, `name`, `timestamp`) VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE id=VALUES(id), oauth_token=VALUES(oauth_token), oauth_token_secret=VALUES(oauth_token_secret), screen_name=VALUES(screen_name), name=VALUES(name), timestamp=VALUES(timestamp)',
                    array($twitterResponse['id'], $auth->getIdentity(), $token['oauth_token'], $token['oauth_token_secret'], $twitterResponse['screen_name'], $twitterResponse['name'], date("Y-m-d H:i:s")));
        
        $twitterInfo_namespace->unlock();
        return true;
    }
    
    public function removeForm()
    {
        static $form = '';
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        if(!is_object($form))
        {
            require_once APPLICATION_PATH . '/forms/removeTwitter.php';
             
            $form = new Form_removeTwitter(array(
                'action' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), "accounttwitter"),
                'method' => 'post',
            ));
        }
        $form->setDefault("hash", $registry->get('globalHash'));
        
        return $form;
    }
    
    public function addForm()
    {
        static $form = '';
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        if(!is_object($form))
        {
            require_once APPLICATION_PATH . '/forms/addTwitter.php';
             
            $form = new Form_addTwitter(array(
                'action' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), "accounttwitter"),
                'method' => 'post',
            ));
        }
        $form->setDefault("hash", $registry->get('globalHash'));
        
        return $form;
    }
}