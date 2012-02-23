<?php

//require_once ('Zend/Controller/Router/Interface.php');
//require_once ('Zend/Controller/Router/Abstract.php');

class Ml_Controller_Router_Cli extends Zend_Controller_Router_Abstract implements 
Zend_Controller_Router_Interface
{
    public function assemble ($userParams, $name = null, $reset = false, $encode = true)
    {
    }
    
    public function route (Zend_Controller_Request_Abstract $dispatcher)
    {
        try {
            $opts = new Zend_Console_Getopt(
                array(
                    'help|h' => 'prints this usage information',
                    'action|a=s' => 'action name (default: index)',
                    'controller|c=s' => 'controller name  (default: index)',
                    'verbose|v' => 'explain what is being done',
                )
            );
            
            $opts->parse();
        } catch (Zend_Console_Getopt_Exception $e) {
            echo($e->getMessage() . "\n\n" . $e->getUsageMessage());
            exit(1);
        }
        
        if ($opts->getOption("help")) {
            echo $opts->getUsageMessage();
            exit;
        }
        
        if (! $opts->getOption("action")) {
            $opts->setOption("action", "index");
        }
        
        if (! $opts->getOption("controller")) {
            $opts->setOption("controller", "index");
        }
        
        $dispatcher
        ->setControllerName($opts->getOption("controller"))
        ->setActionName($opts->getOption("action"))
        ;
    }
}
