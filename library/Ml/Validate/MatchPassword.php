<?php

class Ml_Validate_MatchPassword extends Zend_Validate_Abstract
{
    const INVALID = 'valueInvalid';
    const MSG_WRONG_PASSWORD = 'wrongPassword';

    protected $_messageTemplates = array(
        self::INVALID => "Invalid type given. String expected",
        self::MSG_WRONG_PASSWORD => "Wrong password",
    );

    protected $_credential = null;

    protected $_uid = null;

    public function __construct(Ml_Model_Credential $credential, $uid)
    {
        $this->_credential = $credential;

        $this->_uid = $uid;
    }

    public function isValid($value)
    {
        $this->_setValue($value);

        if (! is_string($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        $adapter = $this->_credential->getAuthAdapter($this->_uid, $value);
        $resp = $adapter->authenticate();

        if ($resp->getCode() != Zend_Auth_Result::SUCCESS) {
            $this->_error(self::MSG_WRONG_PASSWORD);
            return false;
        }
        return true;
    }
}
