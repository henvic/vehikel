<?php

class Ml_Model_Search {
    /**
     * @var array
     */
    protected $_searchConfig;

    /**
     * @param array $config Zend_Config array
     */
    public function __construct($config)
    {
        $this->_searchConfig = $config["services"]["search"];
    }

    /**
     * @param string $index
     * @param string $type
     * @param int $id
     * @param int $parentId
     * @param array $data
     * @return mixed int version on the search engine on success, false otherwise
     */
    public function post($index, $type, $id, $parentId = null, $data)
    {
        $dataJson = json_encode($data);

        $url = $this->_searchConfig["server"] . "/" . rawurlencode($index) . "/" . rawurlencode($type) . "/";
        $url .= rawurlencode($id);

        if ($parentId) {
            $url .= "?parent=" . rawurlencode($parentId);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataJson);

        $response = curl_exec($ch);

        $result = json_decode($response, true);

        if (json_last_error() === JSON_ERROR_NONE && isset($result["ok"]) && isset($result["_version"])) {
            return $result["_version"];
        }

        return false;
    }
}
