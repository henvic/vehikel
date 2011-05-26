<?php
class IndexController extends Zend_Controller_Action
{
    public function indexAction() {
    	echo "There is not enough time.\n\nTry --help\n\n";
    	
    	if($_SERVER['argc'] >= 2) echo "\nTip: have you forgot an --action|-a?\n\n";
    }
    /*
    public function versionAction()
    {
    	echo "version 0.1a\n\n\n";
    }*/
}
