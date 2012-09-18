<?php
class Ml_Model_People
{
    const CACHE_LIFETIME = 60;

    // this number should increase after relevant changes
    const CACHE_OBJECT_VERSION = 0;

    protected $_dbTableName = "people";
    protected $_dbHistoryTableName = "people_history";
    protected $_dbAdapter;
    protected $_dbTable;

    protected $_cache;
    protected $_cachePrefix = "user_";
    protected $_cacheUsernamePrefix = "user_name_";

    public function __construct($config, Zend_Cache_Core $cache)
    {
        $this->_cache = $cache;
        $this->_dbTable = new Zend_Db_Table($this->_dbTableName, $config);
        $this->_dbAdapter = $this->_dbTable->getAdapter();
    }

    public function getByUsername($username, $useCache = true)
    {
        if ($useCache) {
            $cached = $this->_cache->load($this->_cacheUsernamePrefix . $username);
            if ($cached && $cached["cache"] == self::CACHE_OBJECT_VERSION) {
                return $cached;
            }
        }

        $select = $this->_dbTable->select()
            ->where("binary `username` = ?", $username);

        return $this->getDbResult($select);
    }

    public function getByEmail($email)
    {
        $select = $this->_dbTable->select()
            ->where("binary `email` = ?", $email);

        return $this->getDbResult($select);
    }

    /**
     * @param $value value is username or email
     * @param bool $useCache if cache might be used or not, it is ignored if email
     */
    public function get($value, $useCache = true)
    {
        if (strpos($value, '@') === false) {
            return $this->getByUsername($value, $useCache);
        }
        return $this->getByEmail($value);
    }

    public function getById($uid, $useCache = true)
    {
        if ($useCache) {
            $cached = $this->_cache->load($this->_cachePrefix . $uid);
            if ($cached && $cached["cache"] == self::CACHE_OBJECT_VERSION) {
                return $cached;
            }
        }

        $select = $this->_dbTable->select()->where("binary `id` = ?", $uid);

        return $this->getDbResult($select);
    }

    public function update($uid, $data)
    {
        $update = $this->_dbTable->update($data, $this->_dbAdapter->quoteInto("id = ?", $uid));

        if ($update) {
            $this->saveHistorySnapshot($uid);
            //retrieves fresh data renewing the cached values in the process
            $this->getById($uid, false);
            return true;
        }

        return false;
    }

    public function create($username, $name, $email)
    {
        $this->_dbAdapter->beginTransaction();
        try {
            $this->_dbTable->insert(array("alias" => $username, "username" => $username, "name" => $name, "email" => $email));

            $uid = $this->_dbAdapter->lastInsertId();

            if (! $uid) {
                return false;
            }

            $this->saveHistorySnapshot($uid);

            $this->_dbAdapter->commit();
        } catch (Exception $e) {
            $this->_dbAdapter->rollBack();
            throw $e;
        }

        return $uid;
    }

    protected function getDbResult(Zend_Db_Select $sql)
    {
        $userInfo = $this->_dbAdapter->fetchRow($sql);

        if (is_array($userInfo)) {
            $userInfo["cache"] = false;
            $userInfo["avatar_info"] = json_decode($userInfo["avatar_info"], true);

            $this->setCache($userInfo);

            return $userInfo;
        }

        return false;
    }

    /**
     * Sets two caches: one with ID and another with username as keys
     *
     * @param $userInfo
     */
    protected function setCache($userInfo)
    {
        $userInfo["cache"] = self::CACHE_OBJECT_VERSION;

        $this->_cache->save($userInfo, $this->_cachePrefix . $userInfo["id"], array(), self::CACHE_LIFETIME);

        $this->_cache->save(
            $userInfo,
            $this->_cacheUsernamePrefix . $userInfo["username"],
            array(),
            self::CACHE_LIFETIME
        );
    }

    protected function deleteCache($id, $username)
    {
        $this->_cache->remove($this->_cachePrefix . $id);
        $this->_cache->remove($this->_cacheUsernamePrefix . $username);
    }

    protected function saveHistorySnapshot($id)
    {
        //the history is built using a best effort, non-transational way
        $historySql = "INSERT INTO "
            . $this->_dbAdapter->quoteTableAs($this->_dbHistoryTableName)
            . "SELECT UUID() as history_id, "
            . $this->_dbAdapter->quoteTableAs($this->_dbTableName)
            . ".*, CURRENT_TIMESTAMP as change_time FROM "
            . $this->_dbAdapter->quoteTableAs($this->_dbTableName)
            . "WHERE id = :id";

        $this->_dbAdapter->query($historySql, array("id" => $id));
    }
}
