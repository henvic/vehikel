<?php

class GarbageController extends Zend_Controller_Action
{
    public function cleanfilesAction()
    {
        // Clean files left by deleted shares
        // It is assumed that their metadata is stored in a removefiles table
        // in the DB
        
        $removeFiles = Ml_Model_RemoveFiles::getInstance();
        
        $removedNum = $removeFiles->gc();
        
        echo "Cleaned " . $removedNum . " files from storage.\n";
    }
    
    public function cleanantiattackAction()
    {
        $maxAge = 24 * 60 * 60;
        
        $antiAttack = Ml_Model_AntiAttack::getInstance();
        
        $deleted = $antiAttack->gc($maxAge);
        
        echo "Number of rows with age > " . $maxAge .
        " (seconds) deleted in antiattack: " . $deleted . "\n";
    }
    
    public function cleanoldnewusersAction()
    {
        $maxAge = 24 * 60 * 60;
        
        $signUp = Ml_Model_SignUp::getInstance();
        
        $deleted = $signUp->gc($maxAge);
        
        echo "Number of rows with age > " . $maxAge .
        " (seconds) deleted in signUp: " . $deleted . "\n";
    }
    
    public function cleanoldrecoverAction()
    {
        $maxAge = 48 * 60 * 60;
        
        $recover = Ml_Model_Recover::getInstance();
        
        $deleted = $recover->gc($maxAge);
        
        echo "Number of rows with age > " . $maxAge .
        " (seconds) deleted in recover: " . $deleted . "\n";
    }
    
    public function cleanoldemailchangeAction()
    {
        $maxAge = 48 * 60 * 60;
        
        $emailChange = Ml_Model_EmailChange::getInstance();
        
        $deleted = $emailChange->gc($maxAge);
        
        echo "Number of rows with age > " . $maxAge .
        " (seconds) deleted in EmailChange: " . $deleted . "\n";
    }
}