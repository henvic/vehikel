<?php
//require_once 'Zend/Validate/Abstract.php';

class Ml_Validate_UsernameNewUser extends Zend_Validate_Abstract
{
    const MSG_USERNAME_RESERVED = 'usernameReserved';
    const MSG_USERNAME_INVALID = 'usernameInvalid';
    const MSG_USERNAME_EXISTS = 'usernameAlreadyExists';

    protected $_messageTemplates = array(
        self::MSG_USERNAME_RESERVED =>
        "This username is reserved and can not be registered",
        self::MSG_USERNAME_INVALID =>
        "This username is invalid. You can only use a-z, 0-9, _ and - for your username",
        self::MSG_USERNAME_EXISTS => "This username is already in use",
    );

    protected $_people;

    protected $_reservedUsernamesFile;

    public function __construct(Ml_Model_People $people, $reservedUsernamesFile)
    {
        $this->_people = $people;
        $this->_reservedUsernamesFile = $reservedUsernamesFile;
    }

    public function isValid($value)
    {
        $this->_setValue($value);

        $value = (string) $value;

        if (preg_match('#([^a-z0-9_-]+)#is', $value) || $value == '0') {
            $this->_error(self::MSG_USERNAME_INVALID);
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

        $userInfo = $this->_people->getByUsername($value);

        if (! is_array($userInfo)) {
            $this->_error(self::MSG_USERNAME_EXISTS);
            return false;
        }
        return true;
    }
}
