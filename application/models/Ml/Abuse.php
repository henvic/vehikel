<?php
/**
 * 
 * @todo if it's gonna to be used change the system so it doesn't
 * use a SQL DB, but a proper queueing system
 * @author henvic
 *
 */

class Ml_Model_Abuse extends Ml_Model_AccessSingleton
{
    protected static $_dbTableName = "abuse";
    
    /**
     * Singleton instance
     *
     */
    protected static $_instance = null;
    
    public static function form()
    {
        static $form = '';
        
        if (! is_object($form)) {
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $form = new Ml_Form_Abuse(array(
                'action' => $router->assemble(array(), "report_abuse"),
                'method' => 'post',
            ));
        }
        
        return $form;
    }
    
    public function insert($data) {
        return $this->_dbTable->insert($data);
    }
    
    public function getTotal() {
        $select = $this->_dbTable->select();
        
        $abusesNum =
        $this->dbAdapter->fetchOne($this->_dbTable->select()
        ->where("solution = ?", "unsolved")
        ->from($this->_dbTable->getTableName(), 'count(*)'));
        
        return $abusesNum;
    }
    
    public function updateStatus($id, $status)
    {
        $this->_dbTable->update(array("solution" => $status), $id);
    }
    
    public function getById($id)
    {
        return $this->_dbTable->getById($id);
    }
    
    public function getLastOpen($id)
    {
        $select = $this->_dbTable->select()
        ->where("solution = ?", "unsolved")
        ->order("timestamp ASC")->limit(1);
        
        return $this->_dbAdapter->fetchRow($select);
    }
}