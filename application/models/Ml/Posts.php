<?php
class Ml_Model_Posts
{
    use Ml_Model_Db_Table_History;

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
    protected $_maxPictures = 30;

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
     * @var Ml_Model_Numbers
     */
    protected $_numbers;

    /**
     * @var Ml_Model_Picture
     */
    protected $_picture;

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
     * @param GearmanClient $gearmanClient
     * @param Ml_Model_People $people
     * @param Ml_Model_HtmlPurifier $purifier
     * @param Ml_Model_Numbers $numbers
     */
    public function __construct(
        $config,
        GearmanClient $gearmanClient,
        Ml_Model_People $people,
        Ml_Model_HtmlPurifier $purifier,
        Ml_Model_Numbers $numbers,
        Ml_Model_Picture $picture
    )
    {
        $this->_dbTable = new Zend_Db_Table($this->_dbTableName, $config);
        $this->_dbAdapter = $this->_dbTable->getAdapter();

        $this->_gearmanClient = $gearmanClient;

        $this->_people = $people;

        $this->_purifier = $purifier;

        $this->_numbers = $numbers;

        $this->_picture = $picture;
    }

    public function getUniversalId($uid, $postId)
    {
        return mb_strtoupper(
            $this->_numbers->base58Encode($uid) .
                "-" .
                $this->_numbers->base58Encode($postId)
        );
    }

    /**
     * @param $post
     * @return array
     */
    public function getPublicInfo($post)
    {
        $content = [
            "id" => (int) $post["id"],
            "universal_id" => $post["universal_id"],
            "creation" => $post["creation"],
            "title" => $post["make"] . " " . $post["model"] . " " . $post["engine"] . " " . $post["name"],
            "name" => $post["name"],
            "type" => $post["type"],
            "make" => $post["make"],
            "model" => $post["model"],
            "price" => (int) $post["price"],
            "year" => (int) $post["model_year"],
            "engine" => $post["engine"],
            "transmission" => $post["transmission"],
            "fuel" => $post["fuel"],
            "km" => (int) $post["km"],
            "armor" => (bool) $post["armor"],
            "handicapped" => (bool) $post["handicapped"],
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

    public function getPostsIdsByUserId($uid)
    {
        $select = $this->_dbTable->select();

        $select->from($this->_dbTableName, "id");

        $select->where("binary `uid` = ?", $uid);

        $data = $this->_dbAdapter->fetchAll($select);

        $ids = array_map(function ($row) {
            return $row["id"];
        }, $data);

        return $ids;
    }

    /**
     * Returns the available stock for a given user and, optionally, type and status
     * @param $uid
     * @param $type [optional]
     * * @param $status [optional]
     * @return array of available stock
     */

    public function getStockAmountByUserId($uid, $type = false, $status = false)
    {
        $select = $this->_dbTable->select();

        $select->from($this->_dbTableName, ["type", "make", "model", "COUNT(*) as amount"]);

        $select->where("uid = ?", $uid);

        if ($type) {
            $select->where("type = ?", $type);
        }

        if ($status) {
            $select->where("status = ?", $status);
        }

        $select->group(["make", "model"]);
        $select->order(["make", "model"]);

        return $this->_dbTable->getAdapter()->fetchAll($select);
    }

    /**
     * @param $id
     * @return array|bool|false|mixed
     */
    public function getById($id)
    {
        $select = $this->_dbTable->select()->where("binary id = ?", $id);

        $data = $this->_dbAdapter->fetchRow($select);

        if (is_array($data)) {
            $picturesSortingOrder = json_decode($data["pictures_sorting_order"], true);
            unset($data["pictures_sorting_order"]);

            $pictures = $this->_picture->getPictures($data["uid"], $data["id"], Ml_Model_Picture::PICTURE_ACTIVE);

            $sortedPictures = $this->_picture->sortPictures($pictures, $picturesSortingOrder);

            $data["pictures"] = $sortedPictures;
            $data["equipment"] = json_decode($data["equipment"], true);

            return $data;
        }

        return false;
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
            throw new Exception("Impossible to sync with the search database.");
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

        if (isset($data["description"])) {
            $data['description_html_escaped'] = $this->_purifier->purify($data['description']);
        }

        try {
            $this->_dbAdapter->beginTransaction();

            $this->_dbTable->insert($data);

            $id = $this->_dbAdapter->lastInsertId();

            $universalIdData = ["universal_id" => $this->getUniversalId($uid, $id)];

            $this->_dbTable->update($universalIdData, $this->_dbAdapter->quoteInto("id = ?", $id));

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
        if (isset($data["equipment"])) {
            $data["equipment"] = json_encode($data["equipment"]);
        }

        if (isset($data["description"])) {
            $data['description_html_escaped'] = $this->_purifier->purify($data['description']);
        }

        $this->_dbAdapter->beginTransaction();

        $this->_dbTable->update($data, $this->_dbAdapter->quoteInto("id = ?", $id));

        $this->saveHistorySnapshot($id);

        $this->_dbAdapter->commit();

        $updatedPost = $this->getById($id);

        if (! is_array($updatedPost)) {
            return false;
        }

        $this->syncSearch($id);

        return $updatedPost;
    }

    /**
     * @param $postId
     * @param $newPicturesIdsSortingOrder array of picture ids in the new sorting order
     * @return picturesInfo array on success, false in failure
     * @throws Exception
     */
    public function sortPictures($postId, $newPicturesIdsSortingOrder)
    {
        $sorting = json_encode($newPicturesIdsSortingOrder);
        $where = $this->_dbAdapter->quoteInto("id = ?", $postId);
        $update = $this->_dbTable->update(["pictures_sorting_order" => $sorting], $where);
        $this->saveHistorySnapshot($postId);

        return $update;
    }

    /**
     * @param $uid
     * @param $perPage int items per page
     * @param $page
     * @param $type
     * @param $make
     * @param $model
     * @param $status
     * @return Zend_Paginator
     */
    public function getUserStreamPage(
        $uid,
        $perPage,
        $page,
        $type = self::TYPE_NO_FILTER,
        $make = false,
        $model = false,
        $status = self::STATUS_NO_FILTER
    ) {
        $select = $this->_dbTable->select();

        $select->where("uid = ?", $uid);

        if ($type) {
            $select->where("type = ?", $type);
        }

        if ($make) {
            $select->where("make = ?", $make);
        }

        if ($model) {
            $select->where("model = ?", $model);
        }

        if ($status) {
            $select->where("status = ?", $status);
        }

        $select->order("creation DESC");

        $paginator = new Zend_Paginator(
            new Ml_Paginator_Adapter_DbTableSelectWithJsonFields($select, [
                "pictures_sorting_order",
                "equipment"
            ])
        );

        $paginator->setCurrentPageNumber($page);
        $paginator->setDefaultItemCountPerPage($perPage);

        return $paginator;
    }

    /**
     * * Get a list of equipments available for a given category
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
