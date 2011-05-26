<?php
require_once 'Zend/Validate/Abstract.php';

class MLValidator_Invite extends Zend_Validate_Abstract
{
    const INVALID_INVITE = 'invalidInvite';
    const USED_INVITE = 'usedInvite';
    const NOTFOUND_INVITE = 'notfoundInvite';
    const EMPTY_INVITE = 'emptyInvite';

    protected $_messageTemplates = array(
        self::INVALID_INVITE   => "Invalid invite code",
        self::USED_INVITE	   => "This invite code was already used",
        self::NOTFOUND_INVITE  => "Invite not found",
        self::EMPTY_INVITE     => "Type your invite"
    );

    public function isValid($value, $context = null)
    {
    	$registry = Zend_Registry::getInstance();
    	
    	if(isset($context['email']) && $context['email'] && mb_strlen($context['email']) <= 60)
    	{
	    	$SignUp = ML_Signup::getInstance();
    		
	    	$select = $SignUp->select()->where("binary email = ?", mb_strtolower($context['email']));
    		
    		$row = $SignUp->fetchRow($select);
	    	
    		if(is_object($row)) {
    			$registry->set("inviteCompleteBefore", true);
    			return true;
    		}
    	}
    	
        $valueString = (string) $value;
        $this->_setValue($valueString);
        
        if(mb_strlen($value) > 8)
        {
        	$this->_error(self::INVALID_INVITE);
        	return false;
        }
        
        if(empty($value))
        {
        	$this->_error(self::EMPTY_INVITE);
        	return false;
        }
        
        $Invites = new ML_Invites();
        
        $select = $Invites->select()->where("hash = ?", mb_strtolower($value));
        
        $row = $Invites->fetchRow($select);
        
        if(!is_object($row))
        {
        	$this->_error(self::NOTFOUND_INVITE);
        	return false;
        }
        
        $token = $row->toArray();
        
        if($token['used'] && $token['used'] != -1) {
        	$this->_error(self::USED_INVITE);
        	return false;
        }
        
        if($token['used'] == -1)//check if the invite code is for 'more than one person'
        {
        	$registry->set("inviteMultiple", true);
        }
        
        return true;
    }
}