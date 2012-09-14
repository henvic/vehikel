<?php

class Ml_Validate_Username extends Zend_Validate_Abstract
{
    const MSG_USERNAME_NOT_FOUND = 'usernameNotFound';
    const MSG_EMAIL_NOT_FOUND = 'emailNotFound';
    const MSG_USER_NOT_ACTIVE = 'userNotActive';

    protected $_messageTemplates = array(
        self::MSG_USERNAME_NOT_FOUND => "User not found",
        self::MSG_EMAIL_NOT_FOUND => "Email not found",
        self::MSG_USER_NOT_ACTIVE => "Inactive user"
    );

    protected $_people = null;

    protected $_userId = null;

    public function __construct(Ml_Model_People $people)
    {
        $this->_people = $people;
    }

    public function isValid($value)
    {
        $this->_setValue($value);

        $value = (string) $value;

        if (strpos($value, '@') === false) {
            if (preg_match('#([^a-z0-9_-]+)#is', $value) || $value == '0') {
                $this->_error(self::MSG_USERNAME_NOT_FOUND);
                return false;
            }

            $userInfo = $this->_people->getByUsername($value);

            if (! is_array($userInfo)) {
                $this->_error(self::MSG_USERNAME_NOT_FOUND);
                return false;
            }
        } else {
            $userInfo = $this->_people->getByEmail($value);
        }

        if (! is_array($userInfo)) {
            $this->_error(self::MSG_EMAIL_NOT_FOUND);
            return false;
        }

        if (! $userInfo["active"]) {
            $this->_error(self::MSG_USER_NOT_ACTIVE);
            return false;
        }

        $this->_userId = $userInfo["id"];

        return true;
    }

    public function getUserId()
    {
        return $this->_userId;
    }
}