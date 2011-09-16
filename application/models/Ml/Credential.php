<?php
require EXTERNAL_LIBRARY_PATH .  '/phpass-0.3/PasswordHash.php';

class Ml_Model_Credential extends Ml_Model_Db_Table
{
    const PASSWORD_HASH_ITERATION_COUNT = "8";
    
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
    {
    }
    
    
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
        $partHash = self::getPreHash($uid, $password);
        
        $tHasher = new PasswordHash(self::PASSWORD_HASH_ITERATION_COUNT, FALSE);
        
        $hash = $tHasher->HashPassword($partHash);
        
        return $hash;
    }
    
    public function getAuthAdapter($uid, $password)
    {
        $authAdapter = new Ml_Auth_Adapter(Zend_Registry::get('database'));
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
        
        $stmt = $this->getAdapter()
         ->query('INSERT INTO `credentials` (`uid`, `credential`) VALUES (?, ?) ON DUPLICATE KEY UPDATE credential=VALUES(credential)',
         array($uid, $hash));
        
        return $stmt->rowCount();
    }
    
    public function loginForm()
    {
        $registry = Zend_Registry::getInstance();
        
        static $form = '';
        
        $config = $registry->get("config");
        
        if (! is_object($form)) {
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $action = ($config['ssl']) ? 'https://'.$config['webhostssl'] : '';
            
            $action .= $router->assemble(array(), "login");
            
            $form = new Ml_Form_Login(array(
                'action' => $action,
                'method' => 'post',
            ));
        }
        return $form;
    }
    
    public static function logoutForm()
    {
        $registry = Zend_Registry::getInstance();
        
        static $form = '';
        
        $config = $registry->get("config");
        
        if (! is_object($form)) {
            
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $form = new Ml_Form_Logout(array(
                'action' =>
                 'http://'.$config['webhost'] .
                  $router->assemble(array(), "logout"),
                'method' => 'post',
            ));
        }
        return $form;
    }
    
    public function newPasswordForm($uid = false, $securityCode = false)
    {
        $registry = Zend_Registry::getInstance();
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $config = $registry->get("config");
        
        static $form = '';
        
        if (! is_object($form)) {
            if (! $uid) {
                $path = $router->assemble(array(), "password");
            } else {
                $path = $router->assemble(array("confirm_uid" => $uid,
                "security_code" => $securityCode),
                "password_unsigned");
            }
            
            if ($config['ssl']) {
                $action = 'https://' . $config['webhostssl'] . $config['webroot'] . $path;
            } else {
                $action = $config['webroot'] . $path;
            }
            
            $form = new Ml_Form_NewPassword(array('action' => $action,
                'method' => 'post',
            ));
            
        }
        return $form;
    }
    
    
    /**
     * 
     * Checks if there is a link to redirect after sign in ...
     * It has to be a internal link, so it won't accept
     * if it makes the user goes to somewhere else instead
     */
    public function checkLinkToRedirect()
    {
        $registry = Zend_Registry::getInstance();
        
        $config = $registry->get("config");
        
        $redirectAfterLogin = filter_input(INPUT_GET, "redirect_after_login", FILTER_UNSAFE_RAW);
        
        if ($redirectAfterLogin && $redirectAfterLogin != null) {
            $testLink = $redirectAfterLogin;
        } else if (isset($_SERVER['HTTP_REFERER'])) {
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            if ($router->getCurrentRouteName() == "login") {
                $referer = $_SERVER['HTTP_REFERER'];
                $partialLink = explode("?redirect_after_login=", $referer, 2);
                
                if (! isset($partialLink[1])) {
                    return false;
                } else {
                    $testLink = $partialLink[1];
                }
            }
        } else {
            return false;
        }
        
        // the redirection link must start with a '/' and
        // must not end up in the redirector again
        // or be in another host (avoids the use of @)
        
        $thisPage = explode("?", $_SERVER['REQUEST_URI'], 2);
        $thisPage = $thisPage[0];
        
        if (mb_substr($testLink, 0, 1) == '/' && !mb_strpos($testLink, "@") && $thisPage != $testLink) {
            // @todo HTTPS support
            $redirTo = "http://" . $config['webhost'] . $testLink;
            
            Zend_Uri::setConfig(array('allow_unwise' => true));
            
            if (Zend_Uri::check($redirTo)) {
                $testUri = Zend_Uri::factory($redirTo);
                
                $path = $testUri->getPath();
                
                Zend_Uri::setConfig(array('allow_unwise' => false));
                
                return $path;
            }
        }
        
        return false;
    }
}
