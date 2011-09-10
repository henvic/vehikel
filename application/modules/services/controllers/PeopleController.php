<?php
class PeopleController extends Zend_Controller_Action
{
    public function deleteAction()
    {
        $registry = Zend_Registry::getInstance();
        
        $service = new Ml_Service();
        
        $timecheck = new Ml_Timecheck();
        
        $people = new Ml_People();
        
        $peopleDeleted = new Ml_PeopleDeleted();
        
        $service->putString("WARNING!\n========\n");
        
        $service->putString("DON'T type the user data. Use COPY/PASTE.\n");
        
        $service->requestConfirmAction("Delete user");
        
        $timecheck->reset();
        
        $enteredUserId = $service->getInput("Delete User of id: ");
        
        $timecheck->check(60);
        $timecheck->reset();
        
        $enteredUserAlias = $service->getInput("Delete User of alias: ");
        
        $timecheck->check(40);
        
        $userInfo = $people->getById($enteredUserId);
        
        if (! is_array($userInfo)) {
            die("User Not Found by ID.\n");
        }
        
        if ($userInfo['id'] != $enteredUserId) {
            throw new Exception("Wrong ID retrieved?");
        }
        
        if ($userInfo['alias'] != $enteredUserAlias) {
            die("Alias does NOT match user id. Please, be careful.\n");
        }
        
        $service->putString("USER INFORMATION\n=================\n");
        
        $service->putString(print_r($userInfo, true));
        
        $timecheck->reset();
        
        $service->requestConfirmAction("Please DO confirm alias, email, name and id.\n\nDelete this user");
        
        $service->requestConfirmAction("Confirm");
        
        $timecheck->check(180);
        
        $service->putString("Sleeping for three seconds.\nAfter that, deleting the user. Use ^C to cancel\n");
        sleep(3);
        
        $registry->set("canDeleteAccount", true);
        
        $peopleDeleted->deleteAccount($userInfo, sha1(serialize($userInfo)));
        
        echo "User account deleted.\n";
    }
}