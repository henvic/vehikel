<?php

class Ml_Validate_Username extends Zend_Validate_Abstract
{
    const MSG_USERNAME_NOT_FOUND = 'usernameNotFound';
    const MSG_EMAIL_NOT_FOUND = 'emailNotFound';

    protected $_messageTemplates = array(
        self::MSG_USERNAME_NOT_FOUND => "User not found",
        self::MSG_EMAIL_NOT_FOUND => "Email not found",
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

        $string = (string) $value;

        if (mb_strstr($value, "@")) {
            $userInfo = $this->_people->getByEmail($value);

            if (! is_array($userInfo)) {
                $this->_error(self::MSG_EMAIL_NOT_FOUND);
                return false;
            }

            $this->_userId = $userInfo["id"];

            return true;
        }

        if (preg_match('#([^a-z0-9_-]+)#is', $value) || $value == '0') {
            $this->_error(self::MSG_USERNAME_NOT_FOUND);
            return false;
        }

        $userInfo = $this->_people->getByUsername($value);

        if (empty($userInfo)) {
            $this->_error(self::MSG_USERNAME_NOT_FOUND);
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