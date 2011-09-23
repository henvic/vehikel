<?php
class AbuseController extends Zend_Controller_Action
{
    public function getAction()
    {
        $service = new Ml_Model_Service();
        $abuse = Ml_Model_Abuse::getInstance();
        
        $abusesNum = $abuse->getTotal();
        
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
            $row = $abuse->getLastOpen();
        } else {
            $row = $abuse->getById($id);
        }
        
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
        
        $abuse->updateStatus($rowData['id'], $isSolved);
        
        $service->putString("Status changed to $isSolved\n");
    }
}