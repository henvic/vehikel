<?php

trait Ml_Model_Db_CachePeople
{
    /** @var $_cache \Zend_Cache_Core() */
    protected $_cache;

    protected function getCache($id)
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
    protected function setCache($userInfo)
    {
        $userInfo["cache"] = $this->_cacheObjectVersion;

        $this->_cache->save($userInfo, $this->getCachePath($userInfo["id"]), array(), $this->_cacheLifetime);

        $this->_cache->save(
            $userInfo,
            $this->getUsernameCachePath($userInfo["username"]),
            array(),
            $this->_cacheLifetime
        );
    }

    protected function deleteCache($id, $username)
    {
        $this->_cache->remove($this->getCachePath($id));
        $this->_cache->remove($this->getUsernameCachePath($username));
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
