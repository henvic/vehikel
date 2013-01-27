<?php

trait Ml_Model_Db_Cache
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
        return $this->getCache()->load($this->getCachePath($id));
    }

    protected function setCacheForId($id, array $data)
    {
        $data["cache"] = $this->_cacheObjectVersion;

        $this->getCache()->save($data, $this->getCachePath($id), array(), $this->_cacheLifetime);
    }

    protected function deleteCache($id)
    {
        $this->getCache()->remove($this->getCachePath($id));
    }

    protected function getCachePath($id)
    {
        return $this->_cachePrefix . $this->_cacheObjectVersion . "_" . $id;
    }
}
