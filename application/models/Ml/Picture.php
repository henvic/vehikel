<?php

class Ml_Model_Picture
{
    protected $_imageQuality = 70;

    protected $_config;

    protected $_http;

    protected $_dbTable;

    protected $_dbAdapter;

    protected $_dbTableName = "pictures";

    const PICTURE_ACTIVE = "active";
    const PICTURE_REMOVED = "removed";

    /**
     * @param Zend_Config DB array $dbconfig
     * @param Ml_Http $http
     * @param Zend_Config array $config
     */
    public function __construct($dbConfig, $http, $config)
    {
        $this->_config = $config;

        $this->_http = $http;

        $this->_dbTable = new Zend_Db_Table($this->_dbTableName, $dbConfig);

        $this->_dbAdapter = $this->_dbTable->getAdapter();
    }

    protected function getMetaJsonFromThumbor($id)
    {
        $uri = $this->_config["services"]["thumbor"]["server"] . "/unsafe/meta/";

        $uri .= rawurlencode($id);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $uri);

        $response = curl_exec($ch);

        $responseArray = $this->_http->parseResponse($response);

        $metaJson = $responseArray["body"];

        json_decode($metaJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        return $metaJson;
    }

    public function getInfo($imageId)
    {
        $select = $this->_dbTable->select();
        $select->where("picture_id = ?", $imageId);

        $pictureInfo = $this->_dbAdapter->fetchRow($select);

        if (! $pictureInfo) {
            return false;
        }

        $pictureInfo["meta"] = json_decode($pictureInfo["meta"], true);
        $pictureInfo["options"] = json_decode($pictureInfo["options"], true);

        return $pictureInfo;
    }

    public function getPictures($uid, $postId, $status = false)
    {
        $select = $this->_dbTable->select();

        if ($uid) {
            $select->where("uid = ?", $uid);
        }

        if ($postId) {
            $select->where("post_id = ?", $postId);
        }

        if ($status) {
            $select->where("status = ?", $status);
        }

        $data = $this->_dbAdapter->fetchAll($select);

        foreach ($data as $key => $picture) {
            $data[$key]["meta"] = json_decode($picture["meta"], true);
            $data[$key]["options"] = json_decode($picture["options"], true);
        }

        return $data;
    }

    /**
     * @param $pictures array of pictures
     * @param $sortingOrder array of picture_id values
     * @return array of sorted pictures
     */
    public function sortPictures($pictures, $sortingOrder)
    {
        $tmpPictures = [];
        $final = [];

        // create a temporary array with the key as the picture_id
        foreach ($pictures as $eachPicture) {
            $tmpPictures[$eachPicture["picture_id"]] = $eachPicture;
        }

        // read the sorting order, adding to the final array
        // the pictures that are on it and exists, and finally
        // removing them from the array

        if (is_array($sortingOrder)) {
            foreach ($sortingOrder as $sortedPictureId) {
                if (isset($tmpPictures[$sortedPictureId])) {
                    $final[] = $tmpPictures[$sortedPictureId];
                    unset($tmpPictures[$sortedPictureId]);
                }
            }
        }

        // add any picture that was not already added to the sorted order
        // on the final array
        foreach ($tmpPictures as $eachPicture) {
            $final[] = $eachPicture;
        }

        return $final;
    }

    /**
     * @param $source string path to a image
     * @param $uid
     * @param $postId
     * @return picture id on success, false otherwise
     */
    public function create($source, $uid = null, $postId = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_URL, $this->_config["services"]["thumbor"]["server"]. "/image");

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Expect:"
        ]);

        //curl assumes a field value starting with a @ as a file
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            "media"=> "@" . $source . ";filename=picture.jpg"
        ]);

        $response = curl_exec($ch);

        $responseArray = $this->_http->parseResponse($response);

        $headers = $responseArray["headers"];

        if (! isset($headers["Location"])) {
            return false;
        }

        $imageLocation = $headers["Location"];

        $imageLocationExploded = explode("/", $imageLocation);

        if (! isset($imageLocationExploded[2])) {
            return false;
        }

        $imageId = $imageLocationExploded[2];

        $json = $this->getMetaJsonFromThumbor($imageId);

        if (! $json) {
            return false;
        }

        $this->_dbTable->insert(
            [
                "picture_id" => $imageId,
                "uid" => $uid,
                "post_id" => $postId,
                "meta" => $json,
                "status" => true
            ]
        );

        return $imageId;
    }

    public function delete($pictureId, $uid = null, $postId = null)
    {
        $where = [];
        $where[] = $this->_dbAdapter->quoteInto("picture_id = ?", $pictureId);
        $where[] = $this->_dbAdapter->quoteInto("uid = ?", $uid);
        $where[] = $this->_dbAdapter->quoteInto("post_id = ?", $postId);
        return $this->_dbTable->update(["status" => "removed"], $where);
    }

    public function setOptions($pictureId, $options)
    {
        $where = $this->_dbAdapter->quoteInto("picture_id = ?", $pictureId);
        $data = ["options" => json_encode($options)];
        return $this->_dbTable->update($data, $where);
    }

    public function getImageLink($id, $options = "")
    {
        $path = $this->_config["services"]["thumbor"]["cdn"] . "/unsafe/" . $id . $options . "/picture.jpg";

        return $path;
    }
}