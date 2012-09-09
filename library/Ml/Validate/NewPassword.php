<?php

/**
 * Checks if the password is the same as before and fails if it is the case
 */
class Ml_Validate_NewPassword extends Zend_Validate_Abstract
{
    const MSG_SAME_PASSWORD = 'samePassword';

    protected $_messageTemplates = array(
        self::MSG_SAME_PASSWORD => "This is the current password",
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

        //shall not accept the same password as before
        if ($resp->getCode() == Zend_Auth_Result::SUCCESS) {
            $this->_error(self::MSG_SAME_PASSWORD);
            return false;
        }
        return true;
    }
}
