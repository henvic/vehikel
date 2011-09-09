<?php
//require_once 'Zend/Validate/Abstract.php';


class MLValidator_NewPasswordRepeat extends Zend_Validate_Abstract
{
    const NOT_MATCH = 'notMatch';

    protected $_messageTemplates = array(
        self::NOT_MATCH => 'Password confirmation does not match'
    );

    public function isValid($value, $context = null)
    {
        $value = (string) $value;
        $this->_setValue($value);
        
        if (mb_strlen($value) < 6 || mb_strlen($value) > 20) {
            return false;
        }

        if (is_array($context)) {
            if (isset($context['password_confirm'])
                && ($value == $context['password_confirm'])) {
                return true;
            }
        } else if (is_string($context) && ($value == $context)) {
            return true;
        }

        $this->_error(self::NOT_MATCH);
        return false;
    }
}