<?php
class Ml_Model_People
{
    use Ml_Model_Db_Table_History;

    /**
     * @var Ml_Model_Picture
     */
    protected $_picture;

    protected $_dbTableName = "people";
    protected $_dbHistoryTableName = "people_history";
    protected $_dbAdapter;
    protected $_dbTable;

    public function __construct(
        $config,
        Ml_Model_HtmlPurifier $purifier,
        Ml_Model_Picture $picture,
        GearmanClient $gearmanClient
    )
    {
        $this->_purifier = $purifier;

        $this->_dbTable = new Zend_Db_Table($this->_dbTableName, $config);
        $this->_dbAdapter = $this->_dbTable->getAdapter();

        $this->_picture = $picture;

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
            "id" => (int) $userInfo["id"],
            "username" => $userInfo["username"],
            "name" => $userInfo["name"]
        ];

        if ($userInfo["picture"]) {
            $content["picture"] = $this->_picture->getPublicInfo($userInfo["picture"]);
        } else {
            $content["picture"] = null;
        }

        $address = $userInfo["address"];
        $content["where"] =  $address["locality"] . " - " . $address["region"];

        if ($userInfo["account_type"] == "retail") {
            $content["address"] = $address;
        } else {
            $content["address"] = $content["where"];
        }

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

    public function getByUsername($username)
    {
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
     */
    public function get($value)
    {
        if (strpos($value, '@') === false) {
            return $this->getByUsername($value);
        }
        return $this->getByEmail($value);
    }

    public function getById($id)
    {
        $select = $this->_dbTable->select()->where("binary `id` = ?", $id);

        return $this->getDbResult($select);
    }

    public function update($id, $data)
    {
        if (isset($data["post_template"])) {
            $data['post_template_html_escaped'] = $this->_purifier->purify($data['post_template']);
        }

        if (! empty($data)) {
            $this->_dbAdapter->beginTransaction();

            $this->_dbTable->update($data, $this->_dbAdapter->quoteInto("id = ?", $id));

            $this->saveHistorySnapshot($id);

            $this->_dbAdapter->commit();
        }

        $updatedUserInfo = $this->getById($id);

        if (! is_array($updatedUserInfo)) {
            return false;
        }

        $this->syncSearch($updatedUserInfo);
        $this->syncPostsSearch($updatedUserInfo["id"]);

        return $updatedUserInfo;
    }

    public function syncPostsSearch($uid)
    {
        return $this->_gearmanClient->doBackground("syncUserData", $uid);
    }

    /**
     * @param $userInfo
     * @return string
     * @throws Exception
     */
    public function syncSearch($userInfo)
    {
        if (! is_array($userInfo)) {
            throw new Exception("Impossible to sync with the search database.");
        }

        if ($userInfo["active"]) {
            $job = $this->createSearchIndex($userInfo);
        } else {
            $job = $this->deleteSearchIndex($userInfo["id"]);
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
            "index" => "posts",
            "type" => "user",
            "id" => $userInfo["id"],
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
            "index" => "posts",
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

        $userInfo = $this->getById($id);

        $this->syncSearch($userInfo);
        $this->syncPostsSearch($userInfo["id"]);

        return $id;
    }

    protected function getDbResult(Zend_Db_Select $sql)
    {
        $userInfo = $this->_dbAdapter->fetchRow($sql);

        if (is_array($userInfo)) {
            $userInfo["address"] = json_decode($userInfo["address"], true);

            if ($userInfo["picture_id"]) {
                $userInfo["picture"] = $this->_picture->getInfo($userInfo["picture_id"]);
            } else {
                $userInfo["picture"] = null;
            }

            return $userInfo;
        }

        return false;
    }

    /**
     * Deactivate the account
     * @param $uid
     * @return bool true in success, false otherwise
     */
    public function deactivate($uid)
    {
        return $this->update($uid, [
            "email" => null,
            "active" => false
        ]);
    }
}
