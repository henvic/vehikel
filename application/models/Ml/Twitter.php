<?php
class Ml_Model_Twitter extends Ml_Model_AccessSingleton
{
    protected static $_dbTableName = "twitter";
    
    protected static $_dbPrimaryRow = "uid";
    
    /**
     * Singleton instance
     *
     */
    protected static $_instance = null;
    
    public static function form()
    {
        static $form = '';
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        if (! is_object($form)) {
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $shareInfo = $registry->get('shareInfo');
            $userInfo = $registry->get('userInfo');
            
            $form = new Ml_Form_Tweet(array('action' =>
             $router->assemble(array("username"
              => $userInfo['alias'], 
                         "share_id" => $shareInfo['id']), "sharepage_1stpage") .
                          '?tweet',
                 'method' => 'post',
             ));
        }
        $form->setDefault("hash", $registry->get('globalHash'));
        
        $form->setDefault("tweet", $shareInfo['title'] . ' ' .
         $config['URLshortening']['twitterlink'] .
         Ml_Model_Numbers::base58Encode($shareInfo['id']));
        
        return $form;
    }
    
    
    public function tweet($msg)
    {
        $auth = Zend_Auth::getInstance();
        
        $config = self::$_registry->get('config');
        
        $twitterConf = $config['services']['twitter'];
        
        if (! $twitterConf['available']) {
            return array("error" => "unavailable");
        }
        
        $twitterAccount = $this->getSignedUserTwitterAccount();
        
        if (mb_strlen($msg) > 140) {
            return array("error" => "msg_too_long");
        }
        
        if (! is_array($twitterAccount)) {
            return array("error" => "account_not_found");
        }
        
        $twitterObj =
         new EpiTwitter($twitterConf['key'], $twitterConf['secret'],
          $twitterAccount['oauth_token'],
          $twitterAccount['oauth_token_secret']);
        
        $twitterInfo = new Zend_Session_Namespace('twitterInfo');
        $recentHistory = $twitterInfo->tweetsRecentHistory;
        
        if (is_array($recentHistory)) {
            $deleteTimer = array();
            foreach ($recentHistory as $key => $oneTime) {
                if ($oneTime < time() - 700) {
                    $deleteTimer[] = $key;
                }
            }
            if (! empty($deleteTimer)) {
                foreach ($deleteTimer as $keyValue) {
                    unset($recentHistory[$keyValue]);
                }
            }
            
            
        }
        
        if (10 < sizeof($recentHistory)) {
            return array("error" => "too_many_tweets");
        }
        
        $updateStatus =
        $twitterObj->post_statusesUpdate(array('status' => $msg));
        
        $recentHistory[] = time();
        
        $twitterInfo->unlock();
        $twitterInfo->tweetsRecentHistory = $recentHistory;
        $twitterInfo->setExpirationSeconds(3600, "tweetsRecentHistory");
        $twitterInfo->lock();
        
        $response = json_decode(($updateStatus->responseText), true);
        
        //response maybe sometimes a response['error']
        if (!is_array($response)) {
            return false;
        }
        
        return $response;
    }
    
    public function getSignedUserTwitterAccount()
    {
        $auth = Zend_Auth::getInstance();
        
        if (! $auth->hasIdentity()) {
            throw new Exception("Not signed in.");
        }
        
        $twitterInfo = new Zend_Session_Namespace('twitterInfo');
        
        if (! $twitterInfo->isLocked()) {
            $select = $this->_dbTable->select()->where("uid = ?", $auth->getIdentity());
            $row = $this->_dbAdapter->fetchRow($select);
            
            $twitterInfo->account = $row;
            
            $twitterInfo->setExpirationSeconds(86400);//we like fresh data
            
            $twitterInfo->lock();
        }
        
        return $twitterInfo->account;
    }
    
    /**
     * 
     * Associates the twitter account
     * @param string $token
     * @param mixed $twitterInfo twitterInfo data, if it exists
     */
    public function associateAccount($token, $twitterInfo = false)
    {
        $auth = Zend_Auth::getInstance();
        
        $config = self::$_registry->get('config');
        
        $twitterConf = $config['services']['twitter'];
        
        $twitterObj =
         new EpiTwitter($twitterConf['key'], $twitterConf['secret']);
        
        $twitterInfoNamespace = new Zend_Session_Namespace('twitterInfo');
        
        //so next time we want to read it, it fetchs
        //the information again, just in case anything changed
        $twitterInfoNamespace->unlock();
        
        if (! $token) {
            $token = array("oauth_token" => $twitterInfo['oauth_token'],
                "oauth_token_secret" => $twitterInfo['oauth_token_secret']);
        }
        
        $twitterObj->setToken($token['oauth_token'],
         $token['oauth_token_secret']);
        
        $resp = $twitterObj->get_accountVerify_credentials();
        
        $twitterResponse = json_decode($resp->responseText, true);
        
        if (! is_array($twitterResponse) || isset($twitterResponse["error"])) {
            return false;
        }
        
        //@todo put the max sizes of screen_name and name for twitter somewhere else more appropriate
        if (! array_key_exists("id", $twitterResponse) ||
         ! array_key_exists("name", $twitterResponse) ||
         ! array_key_exists("screen_name", $twitterResponse) ||
         mb_strlen($twitterResponse['screen_name']) > 15 ||
         mb_strlen($twitterResponse['name']) > 20) {
                return false;
        }
        
        if ($twitterInfo && $twitterInfo['id'] == $twitterResponse['id'] &&
         $twitterInfo['screen_name'] == $twitterResponse['screen_name'] &&
         $twitterInfo['name'] == $twitterResponse['name']) {
                return $twitterInfo;
        }
        
        $this->_dbAdapter->query('INSERT INTO ' .
         $this->_dbAdapter->quoteTableAs(($this->_dbTable->getTableName())) .
         ' (`id`, `uid`, `oauth_token`, `oauth_token_secret`, `screen_name`, `name`, `timestamp`) VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE id=VALUES(id), oauth_token=VALUES(oauth_token), oauth_token_secret=VALUES(oauth_token_secret), screen_name=VALUES(screen_name), name=VALUES(name), timestamp=VALUES(timestamp)',
                    array($twitterResponse['id'],
                     $auth->getIdentity(),
                     $token['oauth_token'], $token['oauth_token_secret'],
                     $twitterResponse['screen_name'], $twitterResponse['name'],
                     date("Y-m-d H:i:s")));
        
        $twitterInfoNamespace->unlock();
        
        return true;
    }
    
    /**
     * 
     * Dissaciate Twitter account of the given user
     * @param big int $uid the user id of this system, not of Twitter's
     */
    public function disassociateAccount($uid)
    {
        $this->_dbTable->delete($this->_dbAdapter->quoteInto('uid = ?', $uid));
    }
    
    public static function removeForm()
    {
        static $form = '';
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        if (! is_object($form)) {
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $form = new Ml_Form_RemoveTwitter(array(
                'action' => $router->assemble(array(), "accounttwitter"),
                'method' => 'post',
            ));
        }
        $form->setDefault("hash", $registry->get('globalHash'));
        
        return $form;
    }
    
    public static function addForm()
    {
        static $form = '';
        
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        if (! is_object($form)) {
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $form = new Ml_Form_AddTwitter(array(
                'action' => $router->assemble(array(), "accounttwitter"),
                'method' => 'post',
            ));
        }
        
        $form->setDefault("hash", $registry->get('globalHash'));
        
        return $form;
    }
}