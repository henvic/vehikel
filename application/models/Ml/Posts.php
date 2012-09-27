<?php
class Ml_Model_Posts
{
    use Ml_Model_Db_Table_History;
    use Ml_Model_Db_Cache;

    protected $_cacheLifetime = 1;//temporary for development...

    // this number should change after relevant changes
    protected $_cacheObjectVersion = 1;

    protected $_dbTableName = "posts";
    protected $_dbHistoryTableName = "posts_history";
    protected $_dbAdapter;
    protected $_dbTable;

    protected $_cache;
    protected $_cachePrefix = "post_";

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
            $data["pictures"] = json_decode($data["pictures"], true);
            $data["equipment"] = json_decode($data["equipment"], true);

            $data["cache"] = false;

            $this->setCache($data["id"], $data);

            return $data;
        }

        return false;
    }

    /**
     * @param $uid
     * @param $perPage int items per page
     * @param $page
     * @param $onlyActive bool only show active items
     * @return Zend_Paginator
     */
    public function getUserStreamPage($uid, $perPage, $page, $onlyActive = true)
    {
        $select = $this->_dbTable->select();

        $select->where("uid = ?", $uid);

        if ($onlyActive) {
            $select->where("active = 1");
        }

        $select->order("creation DESC");

        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($perPage);

        return $paginator;
    }
}
