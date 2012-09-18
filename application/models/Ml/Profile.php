<?php
class Ml_Model_Profile
{
    use Ml_Model_Db_Table_History;
    use Ml_Model_Db_Cache;

    protected $_cacheLifetime = 60;

    // this number should change after relevant changes
    protected $_cacheObjectVersion = 1;

    protected $_dbTableName = "profile";
    protected $_dbHistoryTableName = "profile_history";
    protected $_dbAdapter;
    protected $_dbTable;

    protected $_cache;
    protected $_cachePrefix = "profile_";

    public function __construct($config, Zend_Cache_Core $cache)
    {
        $this->_cache = $cache;
        $this->_dbTable = new Zend_Db_Table($this->_dbTableName, $config);
        $this->_dbAdapter = $this->_dbTable->getAdapter();
    }

    public function getById($id, $useCache = true)
    {
        if ($useCache) {
            $cached = $this->getCache($id);
            if ($cached) {
                return $cached;
            }
        }

        $select = $this->_dbTable->select()->where("binary `id` = ?", $id);

        return $this->getDbResult($select);
    }

    public function addInfo($id, $data)
    {
        $tryUpdate = $this->update($id, $data);

        if ($tryUpdate) {
            return true;
        }

        $data['id'] = $id;

        $this->_dbTable->insert($data);

        $tryInsert = $this->_dbAdapter->lastInsertId();

        if ($tryInsert) {
            return true;
        }

        return false;
    }

    protected function update($id, $data)
    {
        $update = $this->_dbTable->update($data, $this->_dbAdapter->quoteInto("id = ?", $id));
        
        if ($update) {
            $this->saveHistorySnapshot($id);
            //retrieves fresh data renewing the cached values in the process
            $this->getById($id, false);
            return true;
        }

        return false;
    }

    protected function getDbResult(Zend_Db_Select $sql)
    {
        $data = $this->_dbAdapter->fetchRow($sql);

        if (is_array($data)) {
            $data["cache"] = false;

            $this->setCache($data["id"], $data);

            return $data;
        }

        return false;
    }
}
