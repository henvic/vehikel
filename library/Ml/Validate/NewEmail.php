<?php

class Ml_Validate_NewEmail extends Zend_Validate_Abstract
{
    const INVALID = 'valueInvalid';
    const MSG_EMAIL_EXISTS = 'emailAlreadyExists';

    protected $_messageTemplates = array(
        self::INVALID => "Invalid type given. String expected",
        self::MSG_EMAIL_EXISTS =>
        "There is already another account with this e-mail address.",
    );

    protected $_people;
    protected $_ignoreEmails = array();

    public function __construct(Ml_Model_People $people, $ignoreEmails = array())
    {
        $this->_people = $people;
        $this->_ignoreEmails = $ignoreEmails;
    }

    public function isValid($value)
    {
        $this->_setValue($value);

        if (! is_string($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        if (in_array($value, $this->_ignoreEmails)) {
            return true;
        }

        $userInfo = $this->_people->getByEmail($value);

        if (! is_array($userInfo)) {
            $this->_error(self::MSG_EMAIL_EXISTS);
            return false;
        }

        return true;
    }
}
