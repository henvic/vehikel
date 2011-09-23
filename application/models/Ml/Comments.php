<?php
class Ml_Model_Comments extends Ml_Model_AccessSingleton
{
    protected static $_dbTableName = "comments";
    
    /**
     * Singleton instance
     *
     */
    protected static $_instance = null;
    
    public function getById($id) {
        return $this->_dbTable->getById($id);
    }
    
    public function add($msg, $uid, $share)
    {
        //avoid the unintentional add of the same msg twice by the same user
        $query = $this->_dbTable->select()
        ->where("share = ?", $share['id'])
        ->where("uid = ?", $uid)
        ->where("NOW() < TIMESTAMP(lastModified, '00:00:05')")
        ->where("comments = ?", $msg);
        
        $resp = $this->_dbAdapter->fetchAll($query);
        
        if (is_array($resp)) {
            foreach ($resp as $item) {
                if ($item['comments'] == $msg) {
                    return $item['id'];
                }
            }
        }
        
        $purifier = Ml_Model_HtmlPurifier::getInstance();
        
        $msgFiltered = $purifier->purify($msg);
        
        $this->_dbAdapter
         ->query("INSERT INTO `" . $this->_dbTable->getTableName() .
          "` (`uid`, `share`, `byUid`, `comments`, `comments_filtered`, `timestamp`) SELECT ?, ?, ?, ?, ?, CURRENT_TIMESTAMP FROM DUAL",
          array($uid, $share['id'], $share['byUid'], $msg, $msgFiltered));
        
        return $this->_dbAdapter->lastInsertId();
    }
    
    public function delete($id) {
        return $this->_dbTable->
        delete($this->_dbAdapter->quoteInto("id = ?", $id));
    }
    
    public function update($id, $comments) {
        $purifier = Ml_Model_HtmlPurifier::getInstance();
        
        $commentsFiltered = $purifier->purify($comments);
        
        return $this->_dbTable->update(array("comments" => $comments, 
        "comments_filtered" => $commentsFiltered), 
        $this->_dbAdapter->quoteInto("id = ?", $id));
    }
    
    public function count($shareId)
    {
        $query = $this->_dbAdapter->select()
            ->from($this->_dbTable->getTableName(), 'count(*)')
            ->where("share = ?", $shareId);
        
        return $this->_dbAdapter->fetchOne($query);
    }
    
    public function getCommentsPages($shareId, $perPage, $page)
    {
        $people = Ml_Model_People::getInstance();
        
        $select = $this->_dbTable->select();
        $select
        ->where($this->_dbTable->getTableName() . ".share = ?", $shareId)
        ->order("timestamp ASC");
        
        $people->joinDbTableInfo($select, $this->_dbTable->getTableName(), "uid");
        
        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($perPage);
        
        return $paginator;
    }
    
    public function getCommentPosition($commentId, $shareId, $perPage)
    {
        $this->_dbAdapter->query("set @aaaaa:=0");
        
        $query = $this->_dbAdapter
        ->fetchOne("select position from (select * from(select @aaaaa:=@aaaaa+1 as position, id from comments where share = ? order by timestamp ASC) as positions where id = ?) as position;", 
        array($shareId, $commentId));
        
        $this->_dbAdapter->query("set @aaaaa:=0");
        
        $page = ceil($query / $perPage);
        
        $relPos = $query - ($perPage * ($page - 1));
        
        if ($query) {
            return array("page" => $page,
            "page_position" => $relPos,
            "absolute_position" => $query);
        }
        return false;
    }
    
    public function getRecentCommentsInAccountOf($uid)
    {
        $select = $this->_dbTable->select();
        
        $select
        ->where($this->_dbTable->getTableName().".byUid = ?", $uid);
        
        $select->from($this->_dbTable->getTableName());
        $select->setIntegrityCheck(false);
        
        $select->joinRight("share", "share.id = " . $this->_dbTable->getTableName() .
         ".share AND DATE_ADD(comments.lastModified, INTERVAL 3 DAY) > CURRENT_TIMESTAMP AND share.id = (SELECT MAX(s2.id) FROM share s2 WHERE s2.byUid = share.byUid)",
         array("share.id as share.id", "share.title as share.title", "share.fileSize as share.fileSize", "share.short as share.short", "share.views as share.views"));
         
        /* (?) I need to care about people_deleted also: $select->joinRight("people", "people.id = ".$comments->getTableName().".uid", array("people.id as people.id", "people.alias as people.alias", "people.name as people.name", "people.avatarInfo as people.avatarInfo"));*/
         
        $select->order("comments.lastModified DESC");
        
        $select->group("share.id");
        
        $select->limitPage(1, 10);
        
        $recentComments = $this->_dbTable->fetchAll($select);
        
        if (is_object($recentComments)) {
            return $recentComments->toArray();
        }
        return array();
    }
    
    public static function addForm()
    {
        $registry = Zend_Registry::getInstance();
        
        static $form = '';

        if (! is_object($form)) {
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
            
            //we use #previewComment here because if it is not for publishment, we
            //want to preview what is there and if it is, the user will be redirected 
            //to the permalink anyway, so...
            $form = new Ml_Form_Comment(array(
                'action' => $action.'#commentPreview',
                'method' => 'post',
            ));
        }
        return $form;
    }
    
    public static function deleteForm($commentId)
    {
        $registry = Zend_Registry::getInstance();
        
        static $form = '';
        if (! is_object($form)) {
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $userInfo = self::$_registry->get("userInfo");
            $shareInfo = self::$_registry->get("shareInfo");
            
            $form = new Ml_Form_DeleteComment(array(
                'action' => $router->assemble(array("username" =>
                 $userInfo['alias'],
                 "share_id" => $shareInfo['id'],
                 "comment_id" => $commentId),
                 "deletecomment"),
                'method' => 'post',
            ));
        }

        $form->setDefault("hash", $registry->get('globalHash'));

        return $form;
    }
}
