<?php
/*
 * Checks if the magic cookie is valid for the requesting user
 * 
 * @author Henrique Vicente
 */
require_once 'Zend/Validate/Abstract.php';

class MLValidator_MagicCookies extends Zend_Validate_Abstract
{
	const MSG_MAGIC_COOKIE_INVALID = 'invalidMagicCookie';
	const MSG_MAGIC_COOKIE_INVALID_FORMAT = "invalidFormatMagicCookie";
	const MSG_MAGIC_COOKIE_INVALID_SIZE = 'invalidSizeMagicCookie';
	const MSG_MAGIC_COOKIE_ERROR = "errorMagicCookie";
	const MSG_REFERER_HOST_INVALID = 'invalidRefererHost';
 
    protected $_messageTemplates = array(
        self::MSG_MAGIC_COOKIE_INVALID => "Please send your form again",
        self::MSG_MAGIC_COOKIE_INVALID_FORMAT => "Invalid hash format",
        self::MSG_MAGIC_COOKIE_INVALID_SIZE => "Invalid hash size",
        self::MSG_MAGIC_COOKIE_ERROR => "Error processing hash",
        self::MSG_REFERER_HOST_INVALID => "Request form invalid referer host"
    );
    
    protected $_options = array("allowed_referer_hosts" => array());
    
    public function __construct($options)
    {
    	$this->_options = $options;
    }
	
    public function isValid($ignore_value) //$ignore_value isn't used because it's valid, see: filter
    {
    	$value = filter_input(INPUT_POST, ML_MagicCookies::hash_name, FILTER_UNSAFE_RAW);
    	
	    if(isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']))
    	{
			$referer = Zend_Uri::factory($_SERVER['HTTP_REFERER']);
			
			if(!in_array($referer->getHost(), $this->_options['allowed_referer_hosts']))
			{
				$this->_error(self::MSG_REFERER_HOST_INVALID);
				return false;
			}
    	}
    	
    	$last = ML_MagicCookies::getLast();
    	
    	$MagicCookiesNamespace = new Zend_Session_Namespace('MagicCookies');
    	
    	if($last == $value)
    	{
    		return true;
    	}
    	
    	if(!ctype_xdigit($value))
    	{
    		$this->_error(self::MSG_MAGIC_COOKIE_INVALID_FORMAT);
    		return false;
    	}
    	
    	$hex_value = preg_replace('/[^a-f0-9]/', '', $value);//sanitizing

    	if($hex_value != $value)
    	{
    		$this->_error(self::MSG_MAGIC_COOKIE_ERROR);
    		return false;
    	}
    	
    	if(mb_strlen($hex_value) != ML_MagicCookies::lenght)
    	{
    		$this->_error(self::MSG_MAGIC_COOKIE_INVALID_SIZE);
    		return false;
    	}
    	
    	$auth = Zend_Auth::getInstance();
    	
    	$hashInfo = ML_MagicCookies::getHashInfo($hex_value);
    	
    	if(!$hashInfo)
    	{
    		$this->_error(self::MSG_MAGIC_COOKIE_INVALID);
    		return false;
    	}
    	
    	if(!array_key_exists("uid", $hashInfo) || !array_key_exists("session_id", $hashInfo))
    	{
    		$this->_error(self::MSG_MAGIC_COOKIE_ERROR);
    		return false;
    	}
    	
    	if((!is_null($hashInfo['uid']) && $hashInfo['uid'] == $auth->getIdentity()) || (Zend_Session::getId() == $hashInfo['session_id']))
    	{
    		return true;
    	}
    	
    	$this->_error(self::MSG_MAGIC_COOKIE_INVALID);
    	return false;
    }
}
