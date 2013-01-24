<?php

trait Ml_Model_Db_CachePeople
{
    /** @var $_cache \Zend_Cache_Core() */
    protected $_cache;

    protected function setCache($cache)
    {
        $this->_cache = $cache;
    }

    protected function getCache()
    {
        return $this->_cache;
    }

    protected function getCacheById($id)
    {
        return $this->_cache->load($this->getCachePath($id));
    }

    protected function getCacheByUsername($username)
    {
        return $this->_cache->load($this->getUsernameCachePath($username));
    }

    /**
     * Sets two caches: one with ID and another with username as keys
     *
     * @param $userInfo
     */
    protected function setUserInfoCache($userInfo)
    {
        $userInfo["cache"] = $this->_cacheObjectVersion;

        $this->getCache()->save($userInfo, $this->getCachePath($userInfo["id"]), array(), $this->_cacheLifetime);

        $this->getCache()->save(
            $userInfo,
            $this->getUsernameCachePath($userInfo["username"]),
            array(),
            $this->_cacheLifetime
        );
    }

    protected function deleteCache($id, $username)
    {
        $this->getCache()->remove($this->getCachePath($id));
        $this->getCache()->remove($this->getUsernameCachePath($username));
    }

    protected function getCachePath($id)
    {
        return $this->_cachePrefix . $this->_cacheObjectVersion . "_" . $id;
    }

    protected function getUsernameCachePath($username)
    {
        return $this->_cacheUsernamePrefix . $this->_cacheObjectVersion . "_" . $username;
    }
}
