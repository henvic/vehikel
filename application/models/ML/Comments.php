<?php
class ML_Comments extends ML_getModel
{
    protected $_name = "comments";
    
/**
     * Singleton instance
     *
     * @var Zend_Auth
     */
    protected static $_instance = null;
    
    
    /**
     * Singleton pattern implementation makes "new" unavailable
     *
     * @return void
     */
    //protected function __construct()
    //{}

    /**
     * Singleton pattern implementation makes "clone" unavailable
     *
     * @return void
     */
    protected function __clone()
    {
    }
    
    
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    public function add($msg, $uid, $share)
    {
        //avoid the unintentional add of the same msg twice by the same user
        $query = $this->select()->where("share = ?", $share['id'])
        ->where("uid = ?", $uid)
        ->where("NOW() < TIMESTAMP(lastModified, '00:00:05')")
        ->where("comments = ?", $msg);
        
        $resp = $this->getAdapter()->fetchAll($query);
        
        if (is_array($resp)) {
            foreach ($resp as $item) {
                if ($item['comments'] == $msg) {
                    return $item['id'];
                }
            }
        }
        
        $purifier = ML_HtmlPurifier::getInstance();
        
        $msgFiltered = $purifier->purify($msg);
        
        $this->getAdapter()
         ->query("INSERT INTO `" . $this->_name .
          "` (`uid`, `share`, `byUid`, `comments`, `comments_filtered`, `timestamp`) SELECT ?, ?, ?, ?, ?, CURRENT_TIMESTAMP FROM DUAL",
          array($uid, $share['id'], $share['byUid'], $msg, $msgFiltered));
        
        return $this->getAdapter()->lastInsertId();
    }
    
    public function count($shareId)
    {
        $query = $this->select()
            ->from($this->_name, 'count(*)')
            ->where("share = ?", $shareId);
        
        return $this->getAdapter()->fetchOne($query);
    }
    
    public function getCommentsPages($shareId, $perPage, $page)
    {
        $select = $this->select();
        $select
        ->where($this->_name.".share = ?", $shareId)
        ->order("timestamp ASC");
        
        $this->joinPeopleInfo($select, $this->_name, "uid");
        
        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($perPage);
        
        return $paginator;
    }
    
    public function getCommentPosition($commentId, $shareId, $perPage)
    {
        $this->getAdapter()->query("set @aaaaa:=0");
        
        $query = $this->getAdapter()
        ->fetchOne("select position from (select * from(select @aaaaa:=@aaaaa+1 as position, id from comments where share = ? order by timestamp ASC) as positions where id = ?) as position;", 
        array($shareId, $commentId));
        
        $this->getAdapter()->query("set @aaaaa:=0");
        
        $page = ceil($query/$perPage);
        
        $relPos = $query - ($perPage*($page - 1));
        
        if ($query) {
            return array("page" => $page, "page_position" => $relPos,
                "absolute_position" => $query);
        }
        return false;
    }
    
    public function _addForm()
    {
        static $form = '';

        if (! is_object($form)) {
            $registry = Zend_Registry::getInstance();
            
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $userInfo = $registry->get("userInfo");
            $shareInfo = $registry->get('shareInfo');
            
            if ($registry->isRegistered('commentInfo')) {
                $action = $router->assemble(array("username" => $userInfo['alias'],
                 "share_id" => $shareInfo['id'],
                 "comment_id" => $registry['commentInfo']['id']),
                 "editcomment");
            
            } else {
                $action = $router->assemble(array("username" => $userInfo['alias'], "share_id" => $shareInfo['id']), "sharepage_1stpage");
            }
             
            require APPLICATION_PATH . '/forms/Comment.php';
            
            //we use #previewComment here because if it is not for publishment, we
            //want to preview what is there and if it is, the user will be redirected 
            //to the permalink anyway, so...
            $form = new Form_Comment(array(
                'action' => $action.'#commentPreview',
                'method' => 'post',
            ));
        }
        return $form;
    }
    
}
