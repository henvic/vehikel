<?php

try {
    $opts = new Zend_Console_Getopt(
        array(
            'help|h' => 'Displays usage information.',
            'action|a=s' => 'Action to perform in format of controller.action (the module is always the service)',
            'verbose|v' => 'Verbose messages will be dumped to the default output.',
            //'development|d' => 'Enables development mode.',
        )
    );
    
    $opts->parse();
} catch (Zend_Console_Getopt_Exception $e) {
    echo($e->getMessage() ."\n\n". $e->getUsageMessage());
    exit(1);
}

if(isset($opts->h)) {
    echo $opts->getUsageMessage();
    exit;
}

$this->unregisterPluginResource("view");
$this->unregisterPluginResource("layout");
$this->unregisterPluginResource("session");

Zend_Controller_Action_HelperBroker::addPath(APPLICATION_PATH .'/controllers/helpers');

$this->bootstrap('FrontController');
$frontController = $this->getResource('FrontController');
$frontController->setParam('noViewRenderer', true);

if(isset($opts->a)) {
    $reqRoute = array_reverse(explode('.',$opts->a));
    
    if(isset($reqRoute[1]))
    {
        $action = $reqRoute[0];
        $controller = $reqRoute[1];
    } else {
        $action = "index";
        $controller = $reqRoute[0];
    }
    
    $module = "service";
    
    $request = new Zend_Controller_Request_Simple($action, $controller, $module);
    
    $frontController->setRequest($request);
    
    require_once LIBRARY_PATH."/ML/RouteCLIModule.php";
    $frontController->setRouter(new Webf_Controller_Router_Cli());
    $frontController->setResponse(new Zend_Controller_Response_Cli());
}

$frontController->addModuleDirectory(APPLICATION_PATH.'/modules');
$frontController->addControllerDirectory(APPLICATION_PATH.'/modules/'.HOST_MODULE.'/controllers');
