<?php
class ProgressController extends Zend_Controller_Action
{
    public function uploadAction ()
    {
        header("Content-Type: application/json");
        
        if(!is_null(filter_input(INPUT_GET, "push", FILTER_UNSAFE_RAW))) {
            $adapter = new Zend_ProgressBar_Adapter_JsPush(
            array('updateMethodName' => 'Zend_ProgressBar_Update', 
            'finishMethodName' => 'Zend_ProgressBar_Finish'));
            do {
                $progress = Zend_File_Transfer_Adapter_Http::getProgress(
                array('progress' => $adapter));
            } while (! $progress['done']);
        } else {
            $adapter = new Zend_ProgressBar_Adapter_JsPull();
            Zend_File_Transfer_Adapter_Http::getProgress(
            array('progress' => $adapter));
        }
        exit();
    }
}