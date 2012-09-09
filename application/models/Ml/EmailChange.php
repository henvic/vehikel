<?php
class Ml_Model_EmailChange
{
    const REQUEST_EXPIRAL_TIME = 86400;

    protected $_cache = null;
    protected $_cachePrefix = "email_change_req_";

    public function __construct(Zend_Cache_Core $cache)
    {
        $this->_cache = $cache;
    }

    public function create($uid, $email)
    {
        $securityCode = sha1($uid . openssl_random_pseudo_bytes(20) . mt_rand() . microtime());

        $data = array("uid" => $uid, "security_code" => $securityCode, "new_email" => $email);

        $saved = $this->_cache->save($data, $this->getCacheKey($uid, $securityCode), array(), self::REQUEST_EXPIRAL_TIME);

        if ($saved) {
            return $data;
        } else {
            return false;
        }
    }

    public function read($uid, $securityCode)
    {
        if (! $this->isCacheKeySane($uid, $securityCode)) {
            return false;
        }

        return $this->_cache->load($this->getCacheKey($uid, $securityCode));
    }

    public function delete($uid, $securityCode)
    {
        if (! $this->isCacheKeySane($uid, $securityCode)) {
            return false;
        }

        return $this->_cache->remove($this->getCacheKey($uid, $securityCode));
    }

    /**
     * Checks if the passed params seems to be sane to be passed as part of cache key
     * @param $uid
     * @param $securityCode
     * @return bool
     */
    protected function isCacheKeySane($uid, $securityCode)
    {
        if (! ctype_digit($uid) || strlen($uid) > 20 || ! ctype_xdigit($securityCode) || strlen($securityCode) != 40) {
            return false;
        }

        return true;
    }

    protected function getCacheKey($uid, $securityCode)
    {
        return $this->_cachePrefix . $uid . "_" . $securityCode;
    }
}

