<?php

trait Ml_Model_Db_Cache
{
    /** @var $_cache \Zend_Cache_Core() */
    protected $_cache;

    {
        return $this->_cache->load($this->getCachePath($id));
    }

    protected function setCache($id, array $data)
    protected function setCacheById($id, array $data)
    {
        $data["cache"] = $this->_cacheObjectVersion;

        $this->_cache->save($data, $this->getCachePath($id), array(), $this->_cacheLifetime);
    }

    protected function deleteCache($id)
    {
        $this->_cache->remove($this->getCachePath($id));
    }

    protected function getCachePath($id)
    {
        return $this->_cachePrefix . $this->_cacheObjectVersion . "_" . $id;
    }
}
