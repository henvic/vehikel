<?php
/**
 * @author henrique
 *
 */
abstract class ML_getModel extends Zend_Db_Table_Abstract
{
    protected $_name;
    
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
    
    public function joinShareInfo(&$select, $parentTable, $shareField)
    {
        $select->from($parentTable);
        $select->setIntegrityCheck(false);
        $select->joinInner("share",
         "share.id = " . $parentTable . "." .
         $shareField,
         array("share.title as share.title",
          "share.byUid as share.byUid",
          "share.fileSize as share.fileSize",
          "share.short as share.short",
          "share.filename as share.filename"));
    }
    
    public function joinPeopleInfo(&$select, $parentTable, $uidField)
    {    
        $select->from($parentTable);
        $select->setIntegrityCheck(false);
        
        $select->joinLeft("people",
         "people.id = " . $parentTable . "." . $uidField,
         array("people.id as people.id",
          "people.alias as people.alias",
          "people.name as people.name",
          "people.avatarInfo as people.avatarInfo"));
         
        $select->joinLeft("people_deleted",
         "people.id IS NULL AND people_deleted.id = " .
         $parentTable . ".".$uidField,
          array("people_deleted.id as people_deleted.id",
           "people_deleted.alias as people_deleted.alias", 
           "people_deleted.name as people_deleted.name"));
    }
}
