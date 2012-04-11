<?php
/**
 * Overwrites the message templates of Zend_Validate_StringLength
 * for invalid messages of types INVALID, TOO_SHORT and TOO_LONG
 */
class Ml_Validate_StringLength extends Zend_Validate_StringLength
{
    protected $_messageTemplates = array(
    self::INVALID => "Invalid type given, value should be a string", 
    self::TOO_SHORT => "Value is less than %min% characters long", 
    self::TOO_LONG => "Keep it up to %max% characters long");
}
