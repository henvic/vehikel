<?php
class Ml_Service
{
    public function putString($string)
    {
        fwrite(STDOUT, $string);
    }
    
    public function getInput($what = '', $chars = 100)
    {
        if ($what) {
            fwrite(STDOUT, "$what: ");
        }
        
        $stdin = fopen('php://stdin', 'r');
        $data  = mb_substr(fgets($stdin, 100), 0, -1);
        fclose($stdin);
        
        return $data;
    }
    
    public function requestConfirmAction($what)
    {
        $confirm = $this->getInput("$what (yes/no)?");
        
        if ($confirm != 'yes') {
            die("Operation canceled.\n");
        }
    }
}