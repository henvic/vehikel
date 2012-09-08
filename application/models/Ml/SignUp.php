<?php

class Ml_Model_SignUp
{
    const REQUEST_EXPIRAL_TIME = 86400;

    protected $_cache = null;
    protected $_cachePrefix = "signup_req_";

    public function __construct(Zend_Cache_Core $cache)
    {
        $this->_cache = $cache;
    }

    /**
     * Create a new sign up request and return data array with security code in success (else false)
     * @param $name
     * @param $email
     * @return array|bool
     */
    public function create($name, $email)
    {
        $securityCode = sha1($email . openssl_random_pseudo_bytes(40) . mt_rand() . microtime());

        $data = array("name" => $name,
            "email" => $email,
            "security_code" => $securityCode);

        $saved = $this->_cache->save($data, $this->_cachePrefix . $securityCode, array(), self::REQUEST_EXPIRAL_TIME);

        if ($saved) {
            return $data;
        } else {
            return false;
        }
    }

    public function read($securityCode)
    {
        if (! ctype_xdigit($securityCode) || strlen($securityCode) != 40) {
            return false;
        }
        return $this->_cache->load($this->_cachePrefix . $securityCode);
    }

    public function delete($securityCode)
    {
        if (! ctype_xdigit($securityCode) || strlen($securityCode) != 40) {
            return false;
        }
        return $this->_cache->remove($this->_cachePrefix . $securityCode);
    }
}
