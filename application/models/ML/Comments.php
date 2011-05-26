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
    {}
    
    
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
		->where("comments = ?", $msg)
		;
		
		$resp = $this->getAdapter()->fetchAll($query);
		
		if(is_array($resp))
		{
			foreach($resp as $item)
			{
				if($item['comments'] == $msg) {
					return $item['id'];
				}
			}
		}
		
		$purifier = ML_HtmlPurifier::getInstance();
		
		$msg_filtered = $purifier->purify($msg);
		
		$this->getAdapter()->query("INSERT INTO `".$this->_name."` (`uid`, `share`, `byUid`, `comments`, `comments_filtered`, `timestamp`) SELECT ?, ?, ?, ?, ?, CURRENT_TIMESTAMP FROM DUAL", array($uid, $share['id'], $share['byUid'], $msg, $msg_filtered));
		
		return $this->getAdapter()->lastInsertId();
	}
	
	public function count($share_id)
	{
		$query = $this->select()->from($this->_name, 'count(*)')->where("share = ?", $share_id);
		return $this->getAdapter()->fetchOne($query);
	}
	
	public function getCommentsPages($share_id, $per_page, $page)
	{
		$select = $this->select();
		$select
		->where($this->_name.".share = ?", $share_id)
		->order("timestamp ASC")
		;
		
		$this->joinPeopleInfo($select, $this->_name, "uid");
		
		$paginator = Zend_Paginator::factory($select);
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage($per_page);
		
		return $paginator;
	}
	
	public function getCommentPosition($comment_id, $share_id, $per_page)
	{
		$this->getAdapter()->query("set @aaaaa:=0");
		
		$query = $this->getAdapter()->fetchOne(
			"select position from (select * from(select @aaaaa:=@aaaaa+1 as position, id from comments where share = ? order by timestamp ASC) as positions where id = ?) as position;", 
		array($share_id, $comment_id));
		
		$this->getAdapter()->query("set @aaaaa:=0");
		
		$page = ceil($query/$per_page);
		
		$rel_pos = $query - ($per_page*($page - 1));
		
		return ($query) ? array("page" => $page, "page_position" => $rel_pos,
				"absolute_position" => $query) : false;
	}
	
	public function _addForm()
	{
		static $form = '';

		if(!is_object($form))
		{
			$registry = Zend_Registry::getInstance();
			$userInfo = $registry->get("userInfo");
			$shareInfo = $registry->get('shareInfo');
			
			if($registry->isRegistered('commentInfo'))
			{
				$action = Zend_Controller_Front::getInstance()->getRouter()->assemble(array("username" => $userInfo['alias'], "share_id" => $shareInfo['id'], "comment_id" => $registry['commentInfo']['id']), "editcomment");
			} else {
				$action = Zend_Controller_Front::getInstance()->getRouter()->assemble(array("username" => $userInfo['alias'], "share_id" => $shareInfo['id']), "sharepage_1stpage");
			}
			 
			require_once APPLICATION_PATH . '/forms/Comment.php';
			
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
