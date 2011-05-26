<?php
require_once 'Zend/Validate/Abstract.php';

class MLValidator_MatchPassword extends Zend_Validate_Abstract
{
	const MSG_WRONG_PASSWORD = 'wrongPassword';
	
    protected $_messageTemplates = array(
        self::MSG_WRONG_PASSWORD => "Wrong password",
    );
 
    public function isValid($value)
    {
        $this->_setValue($value);
 		
        $valueString = (string) $value;
        
        if(mb_strlen($value) < 6 || mb_strlen($value) > 20) return false;
        
        $credentialInfoData = Zend_Registry::getInstance()->get('credentialInfoDataForPasswordChange');
        
        $nowHash = ML_Credential::getHash($credentialInfoData['uid'], $credentialInfoData['membershipdate'] ,$value);
        
        if($nowHash != $credentialInfoData['credential']) {
			$this->_error(self::MSG_WRONG_PASSWORD);
			return false;
		}
        return true;
    }
}