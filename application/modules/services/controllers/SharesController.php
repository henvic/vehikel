<?php
class SharesController extends Zend_Controller_Action
{
	public function deleteAction()
	{
		$registry = Zend_Registry::getInstance();
		
		$Service = new ML_Service();
		
		$Timecheck = new ML_Timecheck();
		
		$Share = new ML_Upload();
		
		$People = new ML_People();
		
		$Service->putString("WARNING! WARNING! WARNING!\n===========================\n");
		
		$Service->requestConfirmAction("Delete share");
		
		$Timecheck->reset();
		
		$share_id = $Service->getInput("Delete share of ID?");
		
		$Timecheck->check(60);
		$Timecheck->reset();
		
		$shareInfo = $Share->getById($share_id);
		
		if(!is_array($shareInfo))
		{
			die("Share not found.\n");
		}
		
		$Service->putString(print_r($shareInfo, true));
		
		$userInfo = $People->getById($shareInfo['byUid']);
		
		$Service->putString("By user alias: ".$userInfo['alias']."\n");
		
		$Service->requestConfirmAction("Delete this share");
		
		$Share->deleteShare($shareInfo, $userInfo);
		
		echo "Share deleted!\n";
	}
}
