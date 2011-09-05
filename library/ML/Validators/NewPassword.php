<?php
require_once 'Zend/Validate/Abstract.php';


class MLValidator_NewPassword extends Zend_Validate_Abstract
{
    const MSG_SAME_PASSWORD = 'samePassword';
    
    protected $_messageTemplates = array(
        self::MSG_SAME_PASSWORD => "This is the current password",
    );
 
    public function isValid($value)
    {
        $Credential = ML_Credential::getInstance();
        
        $this->_setValue($value);
         
        $valueString = (string) $value;
        
        if(mb_strlen($value) < 6 || mb_strlen($value) > 20) return false;
        
        $credentialInfoData = Zend_Registry::getInstance()->get('credentialInfoDataForPasswordChange');
        
        $adapter = $Credential->getAuthAdapter($credentialInfoData['uid'], $value);
        $authenticate = $adapter->authenticate();
        
        //shall not accept the same password as before
        if($authenticate->getCode() == Zend_Auth_Result::SUCCESS)
        {
            $this->_error(self::MSG_SAME_PASSWORD);
            return false;
        }
        return true;
    }
}