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

    protected $_maxPictures = 12;

    protected $_cacheLifetime = 10;

    // this number should change after relevant changes
    protected $_cacheObjectVersion = 1;

    protected $_dbTableName = "posts";
    protected $_dbHistoryTableName = "posts_history";
    protected $_dbAdapter;
    protected $_dbTable;

    protected $_cache;
    protected $_cachePrefix = "post_";

    protected $_gearmanClient;

    protected $_purifier;

    protected $_types = ["car", "motorcycle", "boat"];
    protected $_status = ["staging", "active", "end"];

    public function __construct(
        $config,
        Zend_Cache_Core $cache,
        GearmanClient $gearmanClient,
        Ml_Model_HtmlPurifier $purifier
    )
    {
        $this->_cache = $cache;
        $this->_dbTable = new Zend_Db_Table($this->_dbTableName, $config);
        $this->_dbAdapter = $this->_dbTable->getAdapter();

        $this->_gearmanClient = $gearmanClient;

        $this->_purifier = $purifier;
    }

    public function getPublicInfo($post)
    {
        $content = [
            "id" => $post["id"],
            "creation" => $post["creation"],
            "name" => $post["name"],
            "type" => $post["type"],
            "make" => $post["make"],
            "model" => $post["model"],
            "model_year" => $post["model_year"],
            "price" => (int) $post["price"],
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

    public function getTypes()
    {
        return $this->_types;
    }

    public function getStatuses()
    {
        return $this->_status;
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

    public function update($id, $data)
    {
        if (isset($data["pictures"])) {
            $data["pictures"] = json_encode($data["pictures"]);
        }

        if (isset($data["equipment"])) {
            $data["equipment"] = json_encode($data["equipment"]);
        }

        $data['description_html_escaped'] = $this->_purifier->purify($data['description']);

        $this->_dbTable->update($data, $this->_dbAdapter->quoteInto("id = ?", $id));

        $this->saveHistorySnapshot($id);

        //retrieves fresh data renewing the cached values in the process and return it
        return $this->getById($id, false);
    }

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

                //retrieves fresh data renewing the cached values in the process
                $this->getById($postId, false);

                $this->_dbAdapter->commit();
                return true;
            }
        } catch (Exception $e) {
            $this->_dbAdapter->rollBack();
            throw $e;
        }

        return false;
    }

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
                    //retrieves fresh data renewing the cached values in the process
                    $this->getById($postId, false);
                    $this->_dbAdapter->commit();
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

                //retrieves fresh data renewing the cached values in the process
                $this->getById($postId, false);

                $this->_dbAdapter->commit();
                return $newPicturesValues;
            }
        } catch (Exception $e) {
            $this->_dbAdapter->rollBack();
            throw $e;
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

    public function getMaxPicturesLimit()
    {
        return $this->_maxPictures;
    }
}
