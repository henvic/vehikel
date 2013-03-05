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

    public function __construct($people, $posts, $cache)
    {
        $this->_people = $people;
        $this->_posts = $posts;
        $this->_cache = $cache;
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
}
