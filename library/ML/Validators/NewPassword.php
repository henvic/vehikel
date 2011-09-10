<?php
//require_once 'Zend/Validate/Abstract.php';


class MLValidator_NewPassword extends Zend_Validate_Abstract
{
    const MSG_SAME_PASSWORD = 'samePassword';
    
    protected $_messageTemplates = array(
        self::MSG_SAME_PASSWORD => "This is the current password",
    );
 
    public function isValid($value)
    {
        $registry = Zend_Registry::getInstance();
        
        $credential = Ml_Credential::getInstance();
        
        $this->_setValue($value);
         
        $valueString = (string) $value;
        
        if (mb_strlen($value) < 6 || mb_strlen($value) > 20) {
            return false;
        }
        
        $credInfo = $registry->get('credentialInfoDataForPasswordChange');
        
        $adapter = $credential->getAuthAdapter($credInfo['uid'], $value);
        $resp = $adapter->authenticate();
        
        //shall not accept the same password as before
        if ($resp->getCode() == Zend_Auth_Result::SUCCESS) {
            $this->_error(self::MSG_SAME_PASSWORD);
            return false;
        }
        return true;
    }
}