<?php
class Ml_Model_Posts
{
    use Ml_Model_Db_Table_History;
    use Ml_Model_Db_Cache;

    const TYPE_CAR = "car";
    const TYPE_MOTORCYCLE = "motorcycle";
    const TYPE_BOAT = "boat";
    const TYPE_NO_FILTER = false;

    const STATUS_STAGING = "staging";
    const STATUS_ACTIVE = "active";
    const STATUS_END = "end";
    const STATUS_NO_FILTER = false;

    /**
     * @var int
     */
    protected $_maxPictures = 12;

    /**
     * @var int
     */
    protected $_cacheLifetime = 10;

    /**
     * this cache versioning number should change at significant updates
     * @var int
     */
    protected $_cacheObjectVersion = 1;

    /**
     * @var string
     */
    protected $_dbTableName = "posts";

    /**
     * @var string
     */
    protected $_dbHistoryTableName = "posts_history";

    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_dbAdapter;

    /**
     * @var Zend_Db_Table
     */
    protected $_dbTable;

    /**
     * @var string
     */
    protected $_cachePrefix = "post_";

    /**
     * @var GearmanClient
     */
    protected $_gearmanClient;

    /**
     * @var Ml_Model_People
     */
    protected $_people;

    /**
     * @var Ml_Model_HtmlPurifier
     */
    protected $_purifier;

    /**
     * @var array
     */
    protected $_types = ["car", "motorcycle", "boat"];
    /**
     * @var array
     */
    protected $_status = ["staging", "active", "end"];

    /**
     * @param $config
     * @param Zend_Cache_Core $cache
     * @param GearmanClient $gearmanClient
     * @param Ml_Model_People $people
     * @param Ml_Model_HtmlPurifier $purifier
     */
    public function __construct(
        $config,
        Zend_Cache_Core $cache,
        GearmanClient $gearmanClient,
        Ml_Model_People $people,
        Ml_Model_HtmlPurifier $purifier
    )
    {
        $this->setCache($cache);
        $this->_dbTable = new Zend_Db_Table($this->_dbTableName, $config);
        $this->_dbAdapter = $this->_dbTable->getAdapter();

        $this->_gearmanClient = $gearmanClient;

        $this->_people = $people;

        $this->_purifier = $purifier;
    }

    /**
     * @param $post
     * @return array
     */
    public function getPublicInfo($post)
    {
        $content = [
            "id" => $post["id"],
            "creation" => $post["creation"],
            "title" => $post["make"] . " " . $post["model"] . " " . $post["engine"] . " " . $post["name"],
            "name" => $post["name"],
            "type" => $post["type"],
            "make" => $post["make"],
            "model" => $post["model"],
            "price" => (int) $post["price"],
            "model_year" => (int) $post["model_year"],
            "engine" => $post["engine"],
            "transmission" => $post["transmission"],
            "fuel" => $post["fuel"],
            "km" => $post["km"],
            "armor" => $post["armor"],
            "pictures" => $post["pictures"],
            "equipment" => $post["equipment"],
            "status" => $post["status"],
            "traction" => $post["traction"],
            "description" => $post["description"],
            "description_html_escaped" => $post["description_html_escaped"]
        ];

        return $content;
    }

    /**
     * @return array of available post types
     */
    public function getTypes()
    {
        return $this->_types;
    }

    /**
     * @return array of available post statuses
     */
    public function getStatuses()
    {
        return $this->_status;
    }

    /**
     * @param $id
     * @param bool $useCache
     * @param bool $setCache
     * @return array|bool|false|mixed
     */
    public function getById($id, $useCache = true, $setCache = true)
    {
        if ($useCache) {
            $cached = $this->getCacheById($id);
            if ($cached) {
                return $cached;
            }
        }

        $select = $this->_dbTable->select()->where("binary `id` = ?", $id);

        return $this->getDbResult($select, $setCache);
    }

    /**
     * @param $id
     * @param $userInfo optional userInfo data to avoid getting the userInfo once again
     * @param $post optional post data to avoid getting the post once again
     * @return string
     * @throws Exception
     */
    public function syncSearch($id, $userInfo = null, $post = null)
    {
        if (! $post) {
            $post = $this->getById($id);
        }

        if (! $userInfo) {
            $userInfo = $this->_people->getById($post["uid"]);
        }

        if (! is_array($post) || ! is_array($userInfo)) {
            throw new Exception("Impossible to sync with search database.");
        }

        if ($post["status"] == Ml_Model_Posts::STATUS_ACTIVE) {
            $job = $this->createSearchIndex($post, $userInfo);
        } else {
            $job = $this->deleteSearchIndex($id);
        }

        return $job;
    }

    /**
     * @param $post
     * @param $userInfo
     * @return string
     */
    public function createSearchIndex($post, $userInfo)
    {
        $publicPost = $this->getPublicInfo($post);

        $publicUserInfo = $this->_people->getPublicInfo($userInfo);

        $publicPost["user"] = $publicUserInfo;

        $data = [
            "index" => "posts",
            "type" => "post",
            "id" => $post["id"],
            "document" => $publicPost
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
            "type" => "post",
            "id" => $id
        ];

        $job = $this->_gearmanClient->doBackground("searchDelete", json_encode($data));

        return $job;
    }

    /**
     * @param $uid
     * @param $data
     * @return string
     * @throws Exception
     */
    public function create($uid, $data)
    {
        $data["uid"] = $uid;

        $data["equipment"] = json_encode($data["equipment"]);

        $data["status"] = self::STATUS_STAGING;

        try {
            $this->_dbAdapter->beginTransaction();

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

    /**
     * @param $id
     * @param $data
     * @return array|bool
     */
    public function update($id, $data)
    {
        if (isset($data["pictures"])) {
            $data["pictures"] = json_encode($data["pictures"]);
        }

        if (isset($data["equipment"])) {
            $data["equipment"] = json_encode($data["equipment"]);
        }

        $data['description_html_escaped'] = $this->_purifier->purify($data['description']);

        $this->_dbAdapter->beginTransaction();

        $this->_dbTable->update($data, $this->_dbAdapter->quoteInto("id = ?", $id));

        $this->saveHistorySnapshot($id);

        $this->_dbAdapter->commit();

        //retrieves fresh data renewing the cached values in the process
        $updatedPost = $this->getById($id, false);

        if (! is_array($updatedPost)) {
            return false;
        }

        $this->syncSearch($id);

        return $updatedPost;
    }

    /**
     * @param $pictureInfo
     * @param $postId
     * @return bool
     * @throws Exception
     */
    public function addPicture($pictureInfo, $postId)
    {
        try {
            $this->_dbAdapter->beginTransaction();

            $originalData = $this->getById($postId, false, false);

            if (! $originalData) {
                return false;
            }

            $pictures = $originalData["pictures"];

            $pictures[] = ["id" => $pictureInfo["id"], "secret" => $pictureInfo["secret"]];

            $pictures = json_encode(array_values($pictures));

            $update = $this->_dbTable->update(["pictures" => $pictures], $this->_dbAdapter->quoteInto("id = ?", $postId));

            if ($update) {
                $this->saveHistorySnapshot($postId);
                $this->_dbAdapter->commit();
                $this->syncSearch($postId);
                return true;
            }
        } catch (Exception $e) {
            $this->_dbAdapter->rollBack();
            throw $e;
        }

        return false;
    }

    /**
     * @param $postId
     * @param $pictureId
     * @return bool
     * @throws Exception
     */
    public function deletePicture($postId, $pictureId)
    {
        try {
            $this->_dbAdapter->beginTransaction();

            $post = $this->getById($postId, false, false);

            $found = false;

            foreach ($post["pictures"] as $position => $eachPicture) {
                if ($eachPicture["id"] == $pictureId) {
                    $found = true;
                    break;
                }
            }

            if ($found) {
                unset($post["pictures"][$position]);

                $data = ["pictures" => json_encode(array_values($post["pictures"]))];

                $update = $this->_dbTable->update($data, $this->_dbAdapter->quoteInto("id = ?", $postId));

                if ($update) {
                    $this->saveHistorySnapshot($postId);
                    $this->_dbAdapter->commit();
                    $this->syncSearch($postId);
                    return true;
                }
            }
        } catch (Exception $e) {
            $this->_dbAdapter->rollBack();
            throw $e;
        }

        return false;
    }

    /**
     * @param $postId
     * @param $newPicturesIdsSortingOrder array of picture ids in the new sorting order
     * @return picturesInfo array on success, false in failure
     * @throws Exception
     */
    public function sortPictures($postId, $newPicturesIdsSortingOrder)
    {
        try {
            $this->_dbAdapter->beginTransaction();

            $originalData = $this->getById($postId, false, false);

            if (! $originalData) {
                return false;
            }

            $originalPictures = $originalData["pictures"];

            $newPictures = [];

            foreach ($newPicturesIdsSortingOrder as $eachPictureId) {
                foreach ($originalPictures as $pos => $originalPicture) {
                    if ($originalPicture["id"] == $eachPictureId) {
                        $newPictures[] = $originalPicture;
                        unset($originalPictures[$pos]);
                    }
                }
            }

            $newPictures = array_merge($newPictures, $originalPictures);

            $newPicturesValues = array_values($newPictures);

            $pictures = json_encode($newPicturesValues);

            $update = $this->_dbTable->update(["pictures" => $pictures], $this->_dbAdapter->quoteInto("id = ?", $postId));

            if ($update) {
                $this->saveHistorySnapshot($postId);
                $this->_dbAdapter->commit();
                $this->syncSearch($postId);
                return $newPicturesValues;
            }
        } catch (Exception $e) {
            $this->_dbAdapter->rollBack();
            throw $e;
        }

        return false;
    }

    /**
     * @param Zend_Db_Select $sql
     * @param bool $setCache
     * @return array|bool
     */
    protected function getDbResult(Zend_Db_Select $sql, $setCache = true)
    {
        $data = $this->_dbAdapter->fetchRow($sql);

        if (is_array($data)) {
            $data["pictures"] = json_decode($data["pictures"], true);
            $data["equipment"] = json_decode($data["equipment"], true);

            $data["cache"] = false;

            if ($setCache) {
                $this->setCacheForId($data["id"], $data);
            }

            return $data;
        }

        return false;
    }

    /**
     * @param $uid
     * @param $perPage int items per page
     * @param $page
     * @param $typeFilter
     * @param $statusFilter
     * @return Zend_Paginator
     */
    public function getUserStreamPage($uid, $perPage, $page, $typeFilter = self::TYPE_NO_FILTER, $statusFilter = self::STATUS_NO_FILTER)
    {
        $select = $this->_dbTable->select();

        $select->where("uid = ?", $uid);

        if ($typeFilter) {
            $select->where("type = ?", $typeFilter);
        }

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

    /**
     * @param $category
     * @return array
     */
    public function getAvailableEquipment($category)
    {
        $contents = file_get_contents(APPLICATION_PATH . "/configs/available-equipment.json");

        $allEquipments = json_decode($contents, true);

        if (isset($allEquipments[$category])) {
            $equipments = $allEquipments[$category];
        } else {
            $equipments = array();
        }

        return $equipments;
    }

    /**
     * @return int
     */
    public function getMaxPicturesLimit()
    {
        return $this->_maxPictures;
    }
}
