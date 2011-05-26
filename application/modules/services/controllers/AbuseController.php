<?php
class AbuseController extends Zend_Controller_Action
{
	public function getAction()
	{
		$Service = new ML_Service();
		$Abuse = new ML_Abuse();
		
		$select = $Abuse->select();
		
		$abuses_num = $Abuse->getAdapter()->fetchOne($Abuse->select()->where("solution = ?", "unsolved")->from($Abuse->getTableName(), 'count(*)'));
		
		$Service->putString("Number of abuses waiting for solution: $abuses_num\n\n");
		
		$id = ($abuses_num == 1) ? "" : $Service->getInput("Abuse ID (enter to unsolved oldest)? ");
		if($id == "" && $abuses_num == 0) {
			die;
		}
		if(empty($id))
		{
			$select->where("solution = ?", "unsolved");
			$select->order("timestamp ASC")->limit(1);
		} else {
			$select->where("id = ?", $id);
		}
		
		$row = $Abuse->fetchRow($select);
		
		if(!is_object($row)) {
			die("Nothing to solve.\n");
		}
		
		$row_data = $row->toArray();
		
		$Service->putString(print_r($row_data, true));
		
		$is_solved = $Service->getInput("Change solution status (unsolved/solved/notabuse)? ");
		
		switch($is_solved)
		{
			case "unsolved" : case "solved" : case "notabuse" : break;
			default : die("Status not changed.\n");
		}
		
		$Abuse->update(array("solution" => $is_solved), $row_data['id']);
		
		$Service->putString("Status changed to $is_solved\n");
	}
}