<?php

class Ml_Validate_AccountRecover extends Zend_Validate_Abstract
{
    const MSG_USERNAME_NOT_FOUND = 'usernameNotFound';
    const MSG_EMAIL_NOT_FOUND = 'emailNotFound';

    protected $_messageTemplates = array(
        self::MSG_USERNAME_NOT_FOUND => "No user by this username was found.",
        self::MSG_EMAIL_NOT_FOUND => "No user using this email was found.",
    );

    protected $_people = null;

    public function __construct(Ml_Model_People $people)
    {
        $this->_people = $people;
    }

    public function isValid($value)
    {
        $this->_setValue($value);

        $valueString = (string) $value;

        $userInfo = $this->_people->get($value);

        if (! empty($userInfo)) {
            return true;
        }

        if (strpos($value, '@') === false) {
            $this->_error(self::MSG_USERNAME_NOT_FOUND);
        } else {
            $this->_error(self::MSG_EMAIL_NOT_FOUND);
        }
        return false;
    }
}