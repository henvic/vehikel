<?php

class Ml_Validate_UsernameAvailable extends Zend_Validate_Abstract
{
    const MSG_USERNAME_INVALID = 'usernameInvalid';
    const MSG_USERNAME_EXISTS = 'usernameAlreadyExists';

    protected $_messageTemplates = array(
        self::MSG_USERNAME_INVALID =>
        "This username is invalid. You can only use a-z, 0-9, _ and - for your username",
        self::MSG_USERNAME_EXISTS => "This username is already in use",
    );

    protected $_people;

    public function __construct(Ml_Model_People $people)
    {
        $this->_people = $people;
    }

    public function isValid($value)
    {
        $this->_setValue($value);

        $value = (string) $value;

        if (preg_match('#([^a-z0-9_-]+)#is', $value) || $value == '0') {
            $this->_error(self::MSG_USERNAME_INVALID);
            return false;
        }

        $userInfo = $this->_people->getByUsername($value);

        if (is_array($userInfo)) {
            $this->_error(self::MSG_USERNAME_EXISTS);
            return false;
        }

        return true;
    }
}
