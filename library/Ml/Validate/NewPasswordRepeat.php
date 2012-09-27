<?php

class Ml_Validate_NewPasswordRepeat extends Zend_Validate_Abstract
{
    const INVALID = 'valueInvalid';
    const NOT_MATCH = 'notMatch';

    protected $_messageTemplates = array(
        self::INVALID => "Invalid type given. String expected",
        self::NOT_MATCH => 'A confirmação de senha não corresponde à senha'
    );

    public function isValid($value, $context = null)
    {
        $this->_setValue($value);

        if (! is_string($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        if (! isset($context["password_confirm"])) {
            throw new Exception("password_confirm shall be set for using the NewPasswordRepeat validate");
        }

        if ($value != $context["password_confirm"]) {
            $this->_error(self::NOT_MATCH);
            return false;
        }

        return true;
    }
}
