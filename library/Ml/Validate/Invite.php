<?php
//require_once 'Zend/Validate/Abstract.php';

class Ml_Validate_Invite extends Zend_Validate_Abstract
{
    const INVALID_INVITE = 'invalidInvite';
    const USED_INVITE = 'usedInvite';
    const NOTFOUND_INVITE = 'notfoundInvite';
    const EMPTY_INVITE = 'emptyInvite';

    protected $_messageTemplates = array(
        self::INVALID_INVITE   => "Invalid invite code",
        self::USED_INVITE       => "This invite code was already used",
        self::NOTFOUND_INVITE  => "Invite not found",
        self::EMPTY_INVITE     => "Type your invite"
    );

    public function isValid($value, $context = null)
    {
        $registry = Zend_Registry::getInstance();
        
        if (isset($context['email']) && $context['email'] &&
         mb_strlen($context['email']) <= 60) {
            $signUp = Ml_Model_SignUp::getInstance();
            
            $emailData = $signUp->getByEmail(mb_strtolower($context['email']));
            
            if (! $emailData) {
                $registry->set("inviteCompleteBefore", true);
                return true;
            }
        }
        
        $valueString = (string) $value;
        $this->_setValue($valueString);
        
        if (mb_strlen($value) > 8) {
            $this->_error(self::INVALID_INVITE);
            return false;
        }
        
        if (empty($value)) {
            $this->_error(self::EMPTY_INVITE);
            return false;
        }
        
        $invites = Ml_Model_Invites::getInstance();
        
        $token = $invites->get($value);
        
        if (! $token) {
            $this->_error(self::NOTFOUND_INVITE);
            return false;
        }
        
        if ($token['used'] && $token['used'] != - 1) {
            $this->_error(self::USED_INVITE);
            return false;
        }
        
        //check if the invite code is for 'more than one person'
        if ($token['used'] == -1) {
            $registry->set("inviteMultiple", true);
        }
        
        return true;
    }
}