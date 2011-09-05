<?php
class PeopleController extends Zend_Controller_Action
{
    public function deleteAction()
    {
        $registry = Zend_Registry::getInstance();
        
        $Service = new ML_Service();
        
        $Timecheck = new ML_Timecheck();
        
        $People = new ML_People();
        
        $PeopleDeleted = new ML_PeopleDeleted();
        
        $Service->putString("WARNING! WARNING! WARNING!\n===========================\n");
        
        $Service->putString("DON'T type the user data. Use COPY/PASTE.\n");
        
        $Service->requestConfirmAction("Delete user");
        
        $Timecheck->reset();
        
        $entered_user_id = $Service->getInput("Delete User of id: ");
        
        $Timecheck->check(60);
        $Timecheck->reset();
        
        $entered_user_alias = $Service->getInput("Delete User of alias: ");
        
        $Timecheck->check(40);
        
        $userInfo = $People->getById($entered_user_id);
        
        if(!is_array($userInfo))
        {
            die("User Not Found by ID.\n");
        }
        
        if($userInfo['id'] != $entered_user_id)
        {
            throw new Exception("Wrong ID retrieved?");
        }
        
        if($userInfo['alias'] != $entered_user_alias)
        {
            die("Alias does NOT match user id. Please, be careful.\n");
        }
        
        $Service->putString("USER INFORMATION\n=================\n");
        
        $Service->putString(print_r($userInfo, true));
        
        $Timecheck->reset();
        
        $Service->requestConfirmAction("Please DO confirm alias, email, name and id.\n\nDelete this user");
        
        $Service->requestConfirmAction("Confirm");
        
        $Timecheck->check(180);
        
        $Service->putString("Sleeping for three seconds.\nAfter that, deleting the user. Use ^C to cancel\n");
        sleep(3);
        
        $registry->set("canDeleteAccount", true);
        
        $PeopleDeleted->deleteAccount($userInfo, sha1(serialize($userInfo)));
        
        echo "User account deleted.\n";
    }
}