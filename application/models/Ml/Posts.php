<?php
class Ml_Model_Posts
{
    use Ml_Model_Db_Table_History;
    use Ml_Model_Db_Cache;

    const STATUS_STAGING = -1;
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_END = 2;
    const STATUS_NO_FILTER = false;

    protected $_cacheLifetime = 10;

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

    public function getById($id, $useCache = true, $setCache = true)
    {
        if ($useCache) {
            $cached = $this->getCache($id);
            if ($cached) {
                return $cached;
            }
        }

        $select = $this->_dbTable->select()->where("binary `id` = ?", $id);

        return $this->getDbResult($select, $setCache);
    }

    public function create($uid, $data)
    {
        $data["uid"] = $uid;

        $data["equipment"] = json_encode($data["equipment"]);

        $data["status"] = self::STATUS_STAGING;

        try {
            $this->_dbTable->insert($data);

            $id = $this->_dbAdapter->lastInsertId();

            $this->saveHistorySnapshot($id);

            $this->_dbAdapter->commit();
        } catch (Exception $e) {
            $this->_dbAdapter->rollBack();
            throw $e;
        }

        return $id;
    }


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

    protected function getDbResult(Zend_Db_Select $sql, $setCache = true)
    {
        $data = $this->_dbAdapter->fetchRow($sql);

        if (is_array($data)) {
            $data["pictures"] = json_decode($data["pictures"], true);
            $data["equipment"] = json_decode($data["equipment"], true);

            $data["cache"] = false;

            if ($setCache) {
                $this->setCache($data["id"], $data);
            }

            return $data;
        }

        return false;
    }

    /**
     * @param $uid
     * @param $perPage int items per page
     * @param $page
     * @param $statusFilter
     * @return Zend_Paginator
     */
    public function getUserStreamPage($uid, $perPage, $page, $statusFilter = self::STATUS_NO_FILTER)
    {
        $select = $this->_dbTable->select();

        $select->where("uid = ?", $uid);

        if ($statusFilter) {
            $select->where("status = ?", $statusFilter);
        }

        $select->order("creation DESC");

        $paginator = new Zend_Paginator(
            new Ml_Paginator_Adapter_DbTableSelectWithJsonFields($select, [
                "pictures",
                "equipment"
            ])
        );

        $paginator->setCurrentPageNumber($page);
        $paginator->setDefaultItemCountPerPage($perPage);

        return $paginator;
    }

    public function getAvailableEquipment($category)
    {
        $contents = file_get_contents(APPLICATION_PATH . "/configs/available-equipment.json");

        $allEquipments = json_decode($contents, true);

        $equipments = $allEquipments[$category];

        return $equipments;
    }
}
