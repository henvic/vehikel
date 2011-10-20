<?php
/**
 * @author henrique
 *
 */
class Ml_Model_Db_Table extends Zend_Db_Table_Abstract
{
    protected $_name;
    protected $_primary;
    
    public function __construct($name = '', $primaryRow = 'id', $config = array())
    {
        $this->_primary = $primaryRow;
        
        $this->setTableName($name);
        
        return parent::__construct($config);
    }
    
    public function setTableName($name)
    {
        if ($name != '') {
            $this->_name = $name;
        }
    }
    
    public function getTableName()
    {
        return $this->_name;
    }
    
    public function getById($id)
    {
        $select = $this->select()
        ->where("binary `id` = ?", $id);
        
        return $this->getAdapter()->fetchRow($select);
    }
}
