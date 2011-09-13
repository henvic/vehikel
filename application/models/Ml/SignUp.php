<?php
class Ml_Model_SignUp extends Ml_Model_Db
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
    {
    }
    
    
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    protected $_name = "newusers";
    
    public function signUpForm()
    {
        static $form = '';
        
        if (! is_object($form)) {
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $form = new Ml_Form_SignUp(array(
                'action' => $router->assemble(array(), "join"),
                'method' => 'post',
            ));
        }
        return $form;
    }
    
    public function newIdentityForm($securityCode)
    {
        static $form = '';
        
        if (! is_object($form)) {
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $form = new Ml_Form_NewIdentity(array(
                'action' =>
                 $router->assemble(array("security_code" => $securityCode),
                 "join_emailconfirm"),
                'method' => 'post',
            ));
        }
        return $form;
    }
    
    public function newUser($name, $email)
    {
        //securitycode is just a random hexnumber
        $securitycode = sha1($name.$email.mt_rand(-54300, 105000).microtime());
        
        $this->getAdapter()->query('INSERT INTO `' . $this->_name .
        '` (`email`, `name`, `timestamp`, `securitycode`) SELECT ?, ?, CURRENT_TIMESTAMP, ? FROM DUAL WHERE NOT EXISTS (select * from `people` where people.email = ?) ON DUPLICATE KEY UPDATE name=VALUES(name), timestamp=VALUES(timestamp), securitycode=VALUES(securitycode)',
        array($email, $name, $securitycode, $email));
        
        return array("name" => $name,
         "email" => $email, "securitycode" => $securitycode);
    }
    
}
