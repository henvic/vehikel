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
        $s3 = new Zend_Service_Amazon_S3($config['services']['S3']['key'], $config['services']['S3']['secret']);
        
        $RemoveFiles = new ML_RemoveFiles();
        
        $select = $RemoveFiles->select();
        $select->order("timestamp ASC")->limit(50);
        
        $del_shares = $RemoveFiles->fetchAll($select);
        
        $deleted_shares = array();
        
        foreach($del_shares->toArray() as $del_share) {
            $object_key = $del_share['alias']."/".$del_share['share']."-".$del_share['download_secret']."/".$del_share['filename'];
            
            if($s3->removeObject($config['services']['S3']['sharesBucket']."/".$object_key))
            {
                $deleted_shares[] = $del_share['id'];
            }
        }
        
        if(!empty($deleted_shares)) {
            $querystring = "DELETE from `".$RemoveFiles->getTableName()."` WHERE ";
            do {
                $line = $RemoveFiles->getAdapter()->quoteInto("id = ?", current($deleted_shares));
                $querystring .= $line;
                
                next($deleted_shares);
                
                if(current($deleted_shares)) $querystring.=" OR ";
                
            } while(current($deleted_shares));
            
            $RemoveFiles->getAdapter()->query($querystring);
        }
        
        echo "Cleaned ".count($deleted_shares)." files from storage.\n";
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
    
    public function cleantableolddata($table_name, $age)
    {//todo similar to this other things
        $getModel = new ML_getModel();
        if(empty($table_name) || !ctype_alnum($table_name)) throw new Exception("Table not given or not accepted.\n");
        
        $num_deleted = $getModel->getAdapter()->delete($table_name, $getModel->getAdapter()->quoteInto("timestamp < ?", date("Y-m-d H:i:s", time()-($age))));
        
        echo "Number of rows with age > $age (seconds) deleted in $table_name: ".$num_deleted."\n";
    }
    
}