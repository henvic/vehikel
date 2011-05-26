<?php
class ML_Credential extends ML_getModel
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
    
	protected $_name = "credentials";
	protected $_primary = "uid";
	
	static public function getHash($uid, $membershipdate, $password)
	{
		//the letters after uid is a hash, shall never be changed
		$hash = hash("sha384", $uid."ßÿ".$password.$membershipdate);
		return $hash;
	}
	
	public function getAuthAdapter(array $userInfo, $password)
    {
    	$authAdapter = new Zend_Auth_Adapter_DbTable(Zend_Registry::get('database'));
		$authAdapter
        ->setTableName('credentials')
        ->setIdentityColumn('uid')
        ->setCredentialColumn('credential')
        //->setCredentialTreatment('ALGO')//we use the class ML_Credential for it
        ;
        
        $hash = self::getHash($userInfo['id'], $userInfo['membershipdate'], $password);
        $authAdapter
	    ->setIdentity($userInfo['id'])
	    ->setCredential($hash)
		;
		
        return $authAdapter;
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
