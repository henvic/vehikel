<?php
class ML_Tags extends ML_getModel
{
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
    
    protected $_name = "tags";

    public static function _form()
    {
        static $form = '';
        
        $registry = Zend_Registry::getInstance();
        
        if (! is_object($form)) {
            $shareInfo = $registry->get('shareInfo');
            $userInfo = $registry->get('userInfo');
            
            $router = Zend_Controller_Front::getInstance()->getRouter();
             
            require APPLICATION_PATH . '/forms/Tags.php';
             
            $form = new Form_Tags(array('action' =>
            $router->assemble(array("username" => $userInfo['alias'], 
                        "share_id" => $shareInfo['id']), "sharepage_1stpage") .
                        '?addtags',
                        'method' => 'post',
            ));
        }
        
        $form->setDefault("hash", $registry->get('globalHash'));

        return $form;
    }
    
    public static function _formDelete()
    {
        static $form = '';
        
        $registry = Zend_Registry::getInstance();
        
        if (! is_object($form)) {
            require_once APPLICATION_PATH . '/forms/DeleteTag.php';
             
            $form = new Form_DeleteTag(array(
                'method' => 'post',
            ));
        }
        
        $form->setDefault("hash", $registry->get('globalHash'));

        return $form;
    }
    
    public function getShareTags ($shareId) {
        $select = $this->select()
        ->where("share = ?", $shareId)
        ->order("timestamp ASC");
        
        return $this->getAdapter()->fetchAll($select);
    }
    
    public function getTagPage($uid, $cleantag, $perPage, $page)
    {
        $select = $this->select();
        $select->where($this->_name.".people = ?", $uid)
        ->where($this->_name.".clean = ?", $cleantag)
        ->order("timestamp ASC");
        
        $this->joinShareInfo($select, $this->_name, "share");
        
        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($perPage);
        
        return $paginator;
    }
    
    public function getUserTags($uid)
    {
        $select = $this->select()->where("people = ?", $uid)
        ->order("timestamp ASC")
        //no ->group("clean"): it would kill the counter below
        ;
        
        $data = $this->fetchAll($select);
        
        $taglist = array();
        
        foreach ($data->toArray() as $item) {
            if (isset($taglist[$item['clean']])) {
                $taglist[$item['clean']] += 1;
            } else {
                $taglist[$item['clean']] = 1;
            }
        }
        
        return $taglist;
    }
}
