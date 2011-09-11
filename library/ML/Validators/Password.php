<?php
//require_once 'Zend/Validate/Abstract.php';


class Ml_Validator_Password extends Zend_Validate_Abstract
{
    const MSG_WRONG_PASSWORD = 'wrongPassword';
    
    protected $_messageTemplates = array(
        self::MSG_WRONG_PASSWORD => "Wrong password",
    );
 
    public function isValid($value, $context = null)
    {    
        $registry = Zend_Registry::getInstance();
        
        $credential = Ml_Credential::getInstance();
        
        $this->_setValue($value);
         
        $valueString = (string) $value;
        
        if(mb_strlen($value) < 6 || mb_strlen($value) > 20) return false;
        
        if(!$registry->isRegistered('loginUserInfo')) return false;
        $loginUserInfo = $registry->get('loginUserInfo');
        
        $adapter = $credential->getAuthAdapter($loginUserInfo['id'], $value);
        
        // Get our authentication adapter and check credentials
        if ($adapter) {
            $auth    = Zend_Auth::getInstance();
            $result  = $auth->authenticate($adapter);
            
            if ($result->isValid()) {
                return true;
            }
            
            $this->_error(self::MSG_WRONG_PASSWORD);
            Ml_AntiAttack::log(Ml_AntiAttack::WRONG_CREDENTIAL);
        }
        
        return false;
    }
}