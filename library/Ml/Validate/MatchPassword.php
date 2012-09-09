<?php

class Ml_Validate_MatchPassword extends Zend_Validate_Abstract
{
    const MSG_WRONG_PASSWORD = 'wrongPassword';

    protected $_messageTemplates = array(
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

        $value = (string) $value;

        $adapter = $this->_credential->getAuthAdapter($this->_uid, $value);
        $resp = $adapter->authenticate();

        if ($resp->getCode() != Zend_Auth_Result::SUCCESS) {
            $this->_error(self::MSG_WRONG_PASSWORD);
            return false;
        }
        return true;
    }
}
