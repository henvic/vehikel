<?php
class Ml_Model_People
{
    use Ml_Model_Db_Table_History;
    use Ml_Model_Db_CachePeople;

    protected $_cacheLifetime = 10;

    // this number should change after relevant changes
    protected $_cacheObjectVersion = 1;

    protected $_dbTableName = "people";
    protected $_dbHistoryTableName = "people_history";
    protected $_dbAdapter;
    protected $_dbTable;

    protected $_cachePrefix = "user_";
    protected $_cacheUsernamePrefix = "user_name_";

    public function __construct($config, Zend_Cache_Core $cache, GearmanClient $gearmanClient)
    {
        $this->setCache($cache);
        $this->_dbTable = new Zend_Db_Table($this->_dbTableName, $config);
        $this->_dbAdapter = $this->_dbTable->getAdapter();

        $this->_gearmanClient = $gearmanClient;
    }

    /**
     * Filter out public info about a given user
     * @param $userInfo
     * @return array
     */
    public function getPublicInfo($userInfo)
    {
        $content = [
            "id" => $userInfo["id"],
            "username" => $userInfo["username"],
            "name" => $userInfo["name"]
        ];

        if (isset($userInfo["avatar_info"]["id"]) && isset($userInfo["avatar_info"]["secret"])) {
            $content["picture"]["id"] = $userInfo["avatar_info"]["id"];
            $content["picture"]["secret"] = $userInfo["avatar_info"]["secret"];
        } else {
            $content["picture"]["id"] = "";
            $content["picture"]["secret"] = "";
        }

        $content["address"] = $userInfo["address"];
        $content["account_type"] = $userInfo["account_type"];

        return $content;
    }

    public function getUsersIds()
    {
        $select = $this->_dbTable->select();

        $select->from($this->_dbTableName, "id");

        $data = $this->_dbAdapter->fetchAll($select);

        $ids = array_map(function ($row) {
            return $row["id"];
        }, $data);

        return $ids;
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
            $cached = $this->getCacheById($id);
            if ($cached) {
                return $cached;
            }
        }

        $select = $this->_dbTable->select()->where("binary `id` = ?", $id);

        return $this->getDbResult($select);
    }

    public function update($id, $data)
    {
        $this->_dbAdapter->beginTransaction();

        $this->_dbTable->update($data, $this->_dbAdapter->quoteInto("id = ?", $id));

        $this->saveHistorySnapshot($id);

        $this->_dbAdapter->commit();

        //retrieves fresh data renewing the cached values in the process
        $updatedUserInfo = $this->getById($id, false);

        if (! is_array($updatedUserInfo)) {
            return false;
        }

        $this->syncSearch($id);

        return $updatedUserInfo;
    }

    /**
     * @param $id
     * @return string
     * @throws Exception
     */
    public function syncSearch($id)
    {
        $userInfo = $this->getById($id);

        if (! is_array($userInfo)) {
            throw new Exception("Impossible to sync with search database.");
        }

        if ($userInfo["active"]) {
            $job = $this->createSearchIndex($userInfo);
        } else {
            $job = $this->deleteSearchIndex($id);
        }

        return $job;
    }

    /**
     * @param $userInfo
     * @return string
     */
    public function createSearchIndex($userInfo)
    {
        $publicUserInfo = $this->getPublicInfo($userInfo);

        $data = [
            "index" => "people",
            "type" => "user",
            "id" => $publicUserInfo["id"],
            "document" => $publicUserInfo
        ];

        $job = $this->_gearmanClient->doBackground("searchIndex", json_encode($data));

        return $job;
    }

    /**
     * @param $id
     * @return string
     */
    public function deleteSearchIndex($id)
    {
        $data = [
            "index" => "people",
            "type" => "user",
            "id" => $id,
        ];

        $job = $this->_gearmanClient->doBackground("searchDelete", json_encode($data));

        return $job;
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

        $this->syncSearch($id);

        return $id;
    }

    protected function getDbResult(Zend_Db_Select $sql)
    {
        $userInfo = $this->_dbAdapter->fetchRow($sql);

        if (is_array($userInfo)) {
            $userInfo["cache"] = false;
            $userInfo["avatar_info"] = json_decode($userInfo["avatar_info"], true);
            $userInfo["address"] = json_decode($userInfo["address"], true);

            $this->setUserInfoCache($userInfo);

            return $userInfo;
        }

        return false;
    }
}
