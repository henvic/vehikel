<?php
//thanks to http://www.oplabo.com/article/13
//USED IN THE Form_NewUser
require_once 'Zend/Validate/Abstract.php';


class MLValidator_EmailCheckUser extends Zend_Validate_Abstract
{
    const MSG_EMAIL_EXISTS = 'emailAlreadyExists';
 
    protected $_messageTemplates = array(
        self::MSG_EMAIL_EXISTS => "There is already another account with this e-mail address.",
    );
 
    public function isValid($value)
    {
        $People = ML_People::getInstance();
        
        $this->_setValue($value);
 
        $valueString = (string) $value;
        
        if(mb_strlen($value) < 3 || mb_strlen($value) > 60) return false;
        
        $getUserByMail = $People->getByEmail($value);
        
           if(!empty($getUserByMail) && $getUserByMail['id'] != Zend_Auth::getInstance()->getIdentity()) {
               $this->_error(self::MSG_EMAIL_EXISTS);
               return false;
           }
        return true;
    }
}
