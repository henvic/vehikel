<?php
class Ml_Model_TypeaheadMakes
{
    protected $_cacheLifetime = 10;

    // this number should change after relevant changes
    protected $_cacheObjectVersion = 1;

    protected $_dbTableName = "list_of_makes";
    protected $_dbAdapter;
    protected $_dbTable;

    /** @var $_cache \Zend_Cache_Core() */
    protected $_cache;
    protected $_cachePath = "list_of_makes";

    public function __construct($config, Zend_Cache_Core $cache)
    {
        $this->_cache = $cache;

        $this->_dbTable = new Zend_Db_Table($this->_dbTableName, $config);
        $this->_dbAdapter = $this->_dbTable->getAdapter();
    }

    public function getAll($useCache = true)
    {
        if ($useCache) {
            $cached = $this->_cache->load($this->getCachePath());
            if ($cached) {
                return $cached;
            }
        }

        $select = $this->_dbTable->select();

        $select
            ->order("make ASC")
        ;

        return $this->getDbResult($select);
    }

    public function getItems($type, $useCache = true)
    {
        $allItems = $this->getAll($useCache);

        if (isset($allItems["cached"])) {
            unset($allItems["cached"]);
        }

        $items = [];
        foreach ($allItems as $item) {
            if (empty($type) || $item[$type]) {
                $items[] = $item["make"];
            }
        }

        return $items;
    }

    protected function getDbResult(Zend_Db_Select $sql)
    {
        $data = $this->_dbAdapter->fetchAll($sql);

        if (is_array($data)) {
            $this->_cache->save($data, $this->getCachePath(), array(), $this->_cacheLifetime);

            return $data;
        }

        return false;
    }

    protected function getCachePath()
    {
        return $this->_cachePath . $this->_cacheObjectVersion . "_";
    }
}
