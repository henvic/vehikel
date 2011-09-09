<?php

class GarbageController extends Zend_Controller_Action
{
    /*
     * public function removeleftoversAction()
    {
        // Remove deleted user leftovers
        //not implemented
        exit;
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        $RemoveUser = new ML_RemoveLeftOvers();
        
        
        $select = $RemoveUser->select();
        $select->order("timestamp ASC")->limit(10);
        
        $old_users = $RemoveUser->fetchAll($select);
    }*/
    
    public function cleanfilesAction()
    {
        // Clean files left by deleted shares
        // It is assumed that their metadata is stored in a removefiles table
        // in the DB
        
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        $s3config = $config['services']['S3'];
        
        $s3 = new Zend_Service_Amazon_S3($s3config['key'], $s3config['secret']);
        
        $removeFiles = new ML_RemoveFiles();
        
        $select = $removeFiles->select();
        $select->order("timestamp ASC")->limit(50);
        
        $delShares = $removeFiles->fetchAll($select);
        
        $deletedShares = array();
        
        foreach ($delShares->toArray() as $delShare) {
            $objectKey = $delShare['alias'] . "/" . $delShare['share'] . "-" .
            $delShare['download_secret'] . "/" . $delShare['filename'];
            
            $object = $s3config['sharesBucket'] . "/" . $objectKey;
            
            if ($s3->removeObject($object)) {
                $deletedShares[] = $delShare['id'];
            }
        }
        
        if (! empty($deletedShares)) {
            $querystring = "DELETE from `" . $removeFiles->getTableName() . "` WHERE ";
            do {
                $line = $removeFiles->getAdapter()->quoteInto("id = ?", current($deletedShares));
                $querystring .= $line;
                
                next($deletedShares);
                
                if (current($deletedShares)) {
                    $querystring .= " OR ";
                }
                
            } while (current($deletedShares));
            
            $removeFiles->getAdapter()->query($querystring);
        }
        
        echo "Cleaned ".count($deletedShares)." files from storage.\n";
    }
    
    public function cleanantiattackAction()
    {
        $this->cleantableolddata("antiattack", 24*60*60);
    }
    
    public function cleanoldnewusersAction()
    {
        $this->cleantableolddata("newusers", 48*60*60);
    }
    
    public function cleanoldrecoverAction()
    {
        $this->cleantableolddata("recover", 48*60*60);
    }
    
    public function cleanoldemailchangeAction()
    {
        $this->cleantableolddata("emailChange", 48*60*60);
    }
    
    public function cleantableolddata($tableName, $age)
    {//todo similar to this other things
        $getModel = new ML_Db();
        if (empty($tableName) || ! ctype_alnum($tableName)) {
            throw new Exception("Table not given or not accepted.\n");
        }
        
        $numDelete = $getModel->getAdapter()
         ->delete($tableName, $getModel->getAdapter()
         ->quoteInto("timestamp < ?", date("Y-m-d H:i:s", time()-($age))));
        
        echo "Number of rows with age > $age (seconds) deleted in $tableName: ".$numDelete."\n";
    }
    
}