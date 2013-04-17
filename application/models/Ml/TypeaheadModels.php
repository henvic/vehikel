<?php
class Ml_Model_TypeaheadModels
{
    protected $_cacheLifetime = 10;

    // this number should change after relevant changes
    protected $_cacheObjectVersion = 1;

    protected $_dbTableName = "list_of_models";
    protected $_dbAdapter;
    protected $_dbTable;

    protected $_cache;
    protected $_cachePrefix = "models_";

    public function __construct($config, Zend_Cache_Core $cache)
    {
        $this->_cache = $cache;

        $this->_dbTable = new Zend_Db_Table($this->_dbTableName, $config);
        $this->_dbAdapter = $this->_dbTable->getAdapter();
    }

    public function getByPart($type, $make, $part, $useCache = true)
    {
        if ($useCache) {
            $cached = $this->_cache->load($this->getCachePath($type, $make, $part));
            if ($cached) {
                return $cached;
            }
        }

        $select = $this->_dbTable->select();

        if ($type) {
            $select->where("type = ?", $type);
        }

        $select->where("make = ?", $make)
            ->where("model LIKE ?", "%" . $part . "%")
        ;

        $select->order("type ASC")
            ->order("make ASC")
            ->order("model ASC")
        ;

        $data = $this->_dbAdapter->fetchAll($select);

        if (is_array($data)) {
            $this->_cache->save($data, $this->getCachePath($type, $make, $part), array(), $this->_cacheLifetime);

            return $data;
        }

        return false;
    }

    protected function getCachePath($type, $make, $part)
    {
        return $this->_cachePrefix . $this->_cacheObjectVersion . "_" . md5($type . "-" . $make . "-" . $part);
    }
}
