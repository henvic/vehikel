<?php
class Ml_Model_People
{
    use Ml_Model_Db_Table_History;
    use Ml_Model_Db_CachePeople;

    protected $_cacheLifetime = 60;

    // this number should change after relevant changes
    protected $_cacheObjectVersion = 1;

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
            $cached = $this->getCacheByUsername($username);
            if ($cached) {
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

    public function update($id, $data)
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

    public function create($username, $name, $email)
    {
        $this->_dbAdapter->beginTransaction();
        try {
            $this->_dbTable->insert(array("username" => $username, "name" => $name, "email" => $email));

            $id = $this->_dbAdapter->lastInsertId();

            if (! $id) {
                return false;
            }

            $this->saveHistorySnapshot($id);

            $this->_dbAdapter->commit();
        } catch (Exception $e) {
            $this->_dbAdapter->rollBack();
            throw $e;
        }

        return $id;
    }

    protected function getDbResult(Zend_Db_Select $sql)
    {
        $userInfo = $this->_dbAdapter->fetchRow($sql);

        if (is_array($userInfo)) {
            $userInfo["cache"] = false;
            $userInfo["avatar_info"] = json_decode($userInfo["avatar_info"], true);
            $userInfo["address"] = json_decode($userInfo["address"], true);

            $this->setCache($userInfo);

            return $userInfo;
        }

        return false;
    }
}
