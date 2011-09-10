<?php
class SharesController extends Zend_Controller_Action
{
    public function deleteAction()
    {
        $registry = Zend_Registry::getInstance();
        
        $service = new Ml_Service();
        
        $timecheck = new Ml_Timecheck();
        
        $share = new Ml_Upload();
        
        $people = new Ml_People();
        
        $service->putString("WARNING!\n========\n");
        
        $service->requestConfirmAction("Delete share");
        
        $timecheck->reset();
        
        $shareId = $service->getInput("Delete share of ID?");
        
        $timecheck->check(60);
        $timecheck->reset();
        
        $shareInfo = $share->getById($shareId);
        
        if (! is_array($shareInfo)) {
            die("Share not found.\n");
        }
        
        $service->putString(print_r($shareInfo, true));
        
        $userInfo = $people->getById($shareInfo['byUid']);
        
        $service->putString("By user alias: ".$userInfo['alias']."\n");
        
        $service->requestConfirmAction("Delete this share");
        
        $share->deleteShare($shareInfo, $userInfo);
        
        echo "Share deleted!\n";
    }
}
