<?php
//require_once 'Zend/Validate/Abstract.php';

class Ml_Validate_HardPassword extends Zend_Validate_Abstract
{
    const MSG_PASSWORD_NOT_HARD = 'passwordNotHard';

    protected $_messageTemplates = array(
        self::MSG_PASSWORD_NOT_HARD => "Por favor use uma senha mais segura",
    );

    public function isValid($value, $context = null)
    {
        $this->_setValue($value);

        $value = (string) $value;

        $others = $context;

        if (! isset($others['password_confirm'])) {
            throw new Exception("Ml_Validate_HardPassword expects the password_confirm key to exist");
        }

        unset($others['password_confirm']);
        unset($others['password']);

        if (isset($others["current_password"])) {
            unset($others["current_password"]);
        }

        if (in_array($value, $others)) {
            $this->_error(self::MSG_PASSWORD_NOT_HARD);
            return false;
        }

        return true;
    }
}