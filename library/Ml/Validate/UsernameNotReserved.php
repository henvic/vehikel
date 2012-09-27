<?php

class Ml_Validate_UsernameNotReserved extends Zend_Validate_Abstract
{
    const INVALID = 'valueInvalid';
    const MSG_USERNAME_RESERVED = 'usernameReserved';

    protected $_messageTemplates = array(
        self::INVALID => "Invalid type given. String expected",
        self::MSG_USERNAME_RESERVED =>
        "This username is reserved and can not be registered",
    );

    protected $_reservedUsernamesFile;

    public function __construct($reservedUsernamesFile)
    {
        $this->_reservedUsernamesFile = $reservedUsernamesFile;
    }

    public function isValid($value)
    {
        $this->_setValue($value);

        if (! is_string($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        $file = file_get_contents($this->_reservedUsernamesFile);

        if (! $file) {
            throw new Exception("Username reserved / blacklist is required and was not given");
        }

        $reservedUsernames = json_decode($file);

        if (! $reservedUsernames) {
            throw new Exception("Error reading the json data");
        }

        if (in_array($value, $reservedUsernames)) {
            $this->_error(self::MSG_USERNAME_RESERVED);
            return false;
        }

        return true;
    }
}
