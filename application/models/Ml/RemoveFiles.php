<?php
class Ml_Model_RemoveFiles extends Ml_Model_AccessSingleton
{
    protected static $_dbTableName = "remove_shares_files";
    
    /**
     * Singleton instance
     *
     */
    protected static $_instance = null;
    
    protected $_name = "remove_shares_files";
    
    public function addFileGc($data)
    {
        $this->_dbTable->insert($data);
    }
    
    public function addFilesGc($uid, $alias)
    {
        $this->_dbAdapter->query("INSERT INTO " .
            $this->_dbAdapter->quoteTableAs($this->getTableName()) .
            " (`id`, `byUid`, `download_secret`, `filename`, `alias`, `timestamp`) SELECT id, byUid, download_secret, filename, " .
            $this->_dbAdapter
            ->quoteInto("?", $alias) . " as alias, CURRENT_TIMESTAMP FROM " . 
            $this->_dbAdapter->quoteTableAs(Ml_Model_Share::$_dbTableName) . " where " .
            $this->_dbAdapter
            ->quoteInto("byUid = ?", $uid));
            
    }
    
    public function gc()
    {
        $config = self::$_registry->get("config");
        
        $s3config = $config['services']['S3'];
        
        $s3 = new Zend_Service_Amazon_S3($s3config['key'], $s3config['secret']);
        
        $select = $this->_dbTable->select();
        $select->order("timestamp ASC")->limit(50);
        
        $delShares = $this->_dbAdapter->fetchAll($select);
        
        $deletedShares = array();
        
        foreach ($delShares as $delShare) {
            $objectKey = $delShare['alias'] . "/" . $delShare['share'] . "-" .
            $delShare['download_secret'] . "/" . $delShare['filename'];
            
            $object = $s3config['sharesBucket'] . "/" . $objectKey;
            
            if ($s3->removeObject($object)) {
                $deletedShares[] = $delShare['id'];
            }
        }
        
        if (! empty($deletedShares)) {
            $querystring = "DELETE from " . 
            $this->_dbAdapter->quoteTableAs(($this->_dbTable->getTableName())) . " WHERE ";
            do {
                $line = $this->_dbAdapter->quoteInto("id = ?", current($deletedShares));
                $querystring .= $line;
                
                next($deletedShares);
                
                if (current($deletedShares)) {
                    $querystring .= " OR ";
                }
                
            } while (current($deletedShares));
            
            $this->_dbAdapter->query($querystring);
        }
        
        $removedNum = count($deletedShares);
        
        return $removedNum;
    }
}