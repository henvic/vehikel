<?php
class AbuseController extends Zend_Controller_Action
{
    public function getAction()
    {
        $service = new ML_Service();
        $abuse = new ML_Abuse();
        
        $select = $abuse->select();
        
        $abusesNum =
         $abuse->getAdapter()->fetchOne($abuse->select()
          ->where("solution = ?", "unsolved")
          ->from($abuse->getTableName(), 'count(*)'));
        
        $service->putString("Number of abuses waiting for solution: $abusesNum\n\n");
        
        if ($abusesNum == 1) {
            $id = "";
        } else {
            $id = $service->getInput("Abuse ID (enter to unsolved oldest)? ");
        }
        
        if ($id == "" && $abusesNum == 0) {
            die;
        }
        if (empty($id)) {
            $select->where("solution = ?", "unsolved");
            $select->order("timestamp ASC")->limit(1);
        } else {
            $select->where("id = ?", $id);
        }
        
        $row = $abuse->fetchRow($select);
        
        if (! is_object($row)) {
            die("Nothing to solve.\n");
        }
        
        $rowData = $row->toArray();
        
        $service->putString(print_r($rowData, true));
        
        $isSolved = $service->getInput("Change solution status (unsolved/solved/notabuse)? ");
        
        switch ($isSolved) {
            case "unsolved":
            case "solved":
            case "notabuse":
                break;
            default :
                die("Status not changed.\n");
                break;
        }
        
        $abuse->update(array("solution" => $isSolved), $rowData['id']);
        
        $service->putString("Status changed to $isSolved\n");
    }
}