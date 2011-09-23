<?php
class Ml_Model_Ignore extends Ml_Model_AccessSingleton
{
    protected static $_dbTableName = "ignore";
    
    /**
     * Singleton instance
     *
     */
    protected static $_instance = null;
    
    public function status($uid, $ignoreUid)
    {
        $select = $this->_dbTable->select()
        ->where("`uid` = ?", $uid)
        ->where("`ignore` = ?", $ignoreUid);
        
        $status = $this->_dbAdapter->fetchRow($select);
        
        return $status;
    }
    
    public function set($uid, $ignoreUid)
    {
        $contacts = Ml_Model_Contacts::getInstance();
        $favorites = Ml_Model_Favorites::getInstance();
        
        $this->_dbAdapter->beginTransaction();
        
        try {
            $contacts->setRelationship($uid, $ignoreUid, Ml_Model_Contacts::RELATIONSHIP_TYPE_NONE);
            $contacts->setRelationship($ignoreUid, $uid, Ml_Model_Contacts::RELATIONSHIP_TYPE_NONE);
            
            $favorites->deleteFavoritesFrom($uid, $ignoreUid);
            
            $this->_dbTable->insert(array("uid" => $uid, "ignore" => $ignoreUid));
            $this->_dbAdapter->commit();
        } catch(Exception $e)
        {
            $this->_dbAdapter->rollBack();
            throw $e;
        }
        
        return $this->_dbAdapter->lastInsertId();
    }
    
    public function remove($uid, $ignoreUid)
    {
          return $this->_dbTable
          ->delete($this->_dbAdapter->quoteInto('`uid` = ?', $uid) .
          $this->_dbAdapter->quoteInto(' AND `ignore` = ?', $ignoreUid));
    }
    
    public function getIgnorePage($uid, $perPage, $page, $reverse = false)
    {
        $people = Ml_Model_People::getInstance();
        
        if ($reverse) {
            $uidF = 'ignore';
            $hasF = 'uid';
        } else {
            $uidF = 'uid';
            $hasF = 'ignore';
        }
        
        $select = $this->select();
        $select
        ->where($this->_dbTable->getTableName() . "." . $uidF . " = ?", $uid)
        ->order($this->_dbTable->getTableName() . ".timestamp DESC");
        
        $people->joinDbTableInfo($select, $this->_dbTable->getTableName(), $hasF);
        
        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($perPage);
        
        return $paginator;
    }
    
    public static function form()
    {
        static $form = '';
        
        if (! is_object($form)) {
            $registry = Zend_Registry::getInstance();
            
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $userInfo = $registry->get("userInfo");
            
            $form = new Ml_Form_Ignore(array(
                'action' => $router->assemble(array("username" =>
                 $userInfo['alias']), "contactRelationshipIgnore"),
                'method' => 'post',
            ));
        }
        return $form;
    }
}