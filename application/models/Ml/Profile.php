<?php
class Ml_Model_Profile extends Ml_Model_AccessSingleton
{
    protected static $_dbTableName = "profile";
    
    /**
     * Singleton instance
     *
     */
    protected static $_instance = null;
    
    public function getById($id)
    {
        $select = $this->_dbTable->select()
        ->where("binary `id` = ?", $id);
        
        return $this->_dbAdapter->fetchRow($select);
    }
    
    
    public function create($id)
    {
        $this->_dbTable->insert(array("id" => $id));
    }
    
    public function update($id, $data)
    {
        $update = $this->_dbTable->update($data, $this->_dbAdapter->quoteInto("id = ?", $id));
        
        if ($update) {
            return true;
        } else {
            return false;
        }
    }
}
