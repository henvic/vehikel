<?php

class Ml_Model_Search {
    /**
     * @var Ml_Model_People
     */
    protected $_people;

    /**
     * @var Ml_Model_Posts
     */
    protected $_posts;

    /**
     * @var Zend_Cache_Core
     */
    protected $_cache;

    /**
     * @var array
     */
    protected $_config;

    /**
     * @param Ml_Model_People $people
     * @param Ml_Model_Posts $posts
     * @param Zend_Cache_Core $cache
     * @param array $config Zend_Config array
     */
    public function __construct($people, $posts, $cache, $config)
    {
        $this->_people = $people;
        $this->_posts = $posts;
        $this->_cache = $cache;
        $this->_config = $config;
    }

    public function syncUserById($id)
    {
        $userInfo = $this->_people->getById($id);

        return $this->syncUser($userInfo);
    }

    public function syncUserByUsername($username)
    {
        $userInfo = $this->_people->getByUsername($username);

        return $this->syncUser($userInfo);
    }

    public function syncUser($userInfo)
    {
        if (! $userInfo) {
            return false;
        }

        $syncUserInfoJob = $this->_people->syncSearch($userInfo);

        if (! $syncUserInfoJob) {
            error_log("Could not sync search db for user id " . (int) $userInfo["id"]);
        }

        $postsIds = $this->_posts->getPostsIdsByUserId($userInfo["id"]);

        $postsJobsSynced = 0;

        foreach ($postsIds as $postId) {
            $job = $this->_posts->syncSearch($postId, $userInfo);

            if (! $job) {
                error_log("Could not sync for post id " . (int) $postId);
            } else {
                $postsJobsSynced += 1;
            }
        }

        unset($job);

        return $postsJobsSynced;
    }

    public function cacheFacetsQuery($timeout = 3600)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_config["webhost"] . "/search-engine?facets");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        $content = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if ($statusCode === 200 && $contentType === "application/json") {
            $contentArray = json_decode($content, true);
            $isSaved = $this->_cache->save($contentArray, "facets_cache", array(), $timeout);

            return $isSaved;
        } else {
            return -1;
        }
    }

}
