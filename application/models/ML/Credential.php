<?php
require EXTERNAL_LIBRARY_PATH .  '/phpass-0.3/PasswordHash.php';

class ML_Credential extends ML_getModel
{
	const PASSWORD_HASH_ITERATION_COUNT = "8";
	
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
    
	protected $_name = "credentials";
	protected $_primary = "uid";
	
	static private function getPreHash($uid, $password)
	{
		return hash("sha384", $uid."-".$password);
	}
	
	static public function setHash($uid, $password)
	{
    	$part_hash = self::getPreHash($uid, $password);
    	
    	$t_hasher = new PasswordHash(self::PASSWORD_HASH_ITERATION_COUNT, FALSE);
    	
    	$hash = $t_hasher->HashPassword($part_hash);
    	
    	return $hash;
	}
	
	public function getAuthAdapter($uid, $password)
    {
    	$authAdapter = new ML_Auth_Adapter(Zend_Registry::get('database'));
    	$authAdapter->setTableName($this->_name)
    	->setIdentityColumn($this->_primary)
    	->setIdentity($uid)
    	->setCredentialColumn("credential")
    	->setCredential(self::getPreHash($uid, $password));
    	
    	return $authAdapter;
    }
    
    public function setCredential($uid, $password)
    {
    	$hash = self::setHash($uid, $password);
    	
    	$stmt = $this->getAdapter()->query('INSERT INTO `credentials` (`uid`, `credential`) VALUES (?, ?) ON DUPLICATE KEY UPDATE credential=VALUES(credential)', array($uid, $hash));
    	
		return $stmt->rowCount();
    }
	
	public function _getLoginForm()
    {
    	$registry = Zend_Registry::getInstance();
    	
        static $form = '';
        
        $config = $registry->get("config");
        
        if(!is_object($form))
        {
        	require_once APPLICATION_PATH . '/forms/LoginForm.php';
        	
        	$action = ($config->ssl) ? 'https://'.$config->webhostssl : '';
        	
        	$action .= Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), "login");
        	
            $form = new LoginForm(array(
                'action' => $action,
                'method' => 'post',
            ));
        }
        return $form;
    }
    
	public function _getLogoutForm()
    {
    	$registry = Zend_Registry::getInstance();
    	
        static $form = '';
        
        $config = $registry->get("config");
        
        if(!is_object($form))
        {
        	require_once APPLICATION_PATH . '/forms/LogoutForm.php';
        	
            $form = new LogoutForm(array(
                'action' => 'http://'.$config->webhost . Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), "logout"),
                'method' => 'post',
            ));
        }
        return $form;
    }
    
    /**
     * 
     * Checks if there is a link to redirect after sign in ...
     * It has to be a internal link, so it won't accept if it makes the user goes to somewhere else instead
     */
    public function checkLinkToRedirect()
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        if(isset($_GET['redirect_after_login']))
        {
            $link = $_GET['redirect_after_login'];
        } elseif(isset($_SERVER['HTTP_REFERER'])) {
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            if($router->getCurrentRouteName() == "login")
            {
                $link = $_SERVER['HTTP_REFERER'];
                $partial_link = explode("?redirect_after_login=",$link, 2);
                
                return (isset($partial_link[1])) ? $partial_link[1] : false;
            }
        } else {
            return false;
        }
        
        if(mb_substr($link, 0, 1) == '/')// the redirect_after_login link MUST start with a '/'
        {
            $redir_to = "http://" . $config->webhost . $link;
            
            Zend_Uri::setConfig(array('allow_unwise' => true));
            
            if(Zend_Uri::check($redir_to))
            {
                $test_uri = Zend_Uri::factory($redir_to);
                
                $path = $test_uri->getPath();
                
                Zend_Uri::setConfig(array('allow_unwise' => false));
                
                return $path;
            }
        }
        
        return false;
    }
}
