<?php
//require_once 'Zend/Validate/Abstract.php';


class Ml_Validate_Password extends Zend_Validate_Abstract
{
    const INVALID = 'valueInvalid';
    const MSG_WRONG_PASSWORD = 'wrongPassword';

    protected $_messageTemplates = array(
        self::INVALID => "Invalid type given. String expected",
        self::MSG_WRONG_PASSWORD => "Wrong password",
    );

    protected $_auth = null;

    protected $_credential = null;

    protected $_usernameValidate = null;

    public function __construct(
            Zend_Auth $auth,
            Ml_Model_Credential $credential,
            Ml_Validate_Username $usernameValidate
    )
    {
        $this->_auth = $auth;

        $this->_credential = $credential;

        $this->_usernameValidate = $usernameValidate;
    }

    public function isValid($value, $context = null)
    {
        $this->_setValue($value);

        if (! is_string($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        $userId = $this->_usernameValidate->getUserId();

        if (! $userId) {
            return false;
        }

        $adapter = $this->_credential->getAuthAdapter($userId, $value);
        $resp = $adapter->authenticate();

        if ($resp->getCode() == Zend_Auth_Result::SUCCESS) {
            return true;
        }

        $this->_error(self::MSG_WRONG_PASSWORD);
        return false;
    }
}
