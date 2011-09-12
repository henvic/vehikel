<?php

class TwitterController extends Zend_Controller_Action
{
    public function tweetAction()
    {
        require_once EXTERNAL_LIBRARY_PATH . '/twitter-async/EpiCurl.php';
        require_once EXTERNAL_LIBRARY_PATH . '/twitter-async/EpiOAuth.php';
        require_once EXTERNAL_LIBRARY_PATH . '/twitter-async/EpiTwitter.php';
        
        $auth = Zend_Auth::getInstance();
        $registry = Zend_Registry::getInstance();
        
        $config = $registry->get("config");
        
        $router = $this->getFrontController()->getRouter();
        
        $userInfo = $registry->get("userInfo");
        $shareInfo = $registry->get("shareInfo");
        
        $request = $this->getRequest();
        $params = $request->getParams();
        
        $twitter = Ml_Model_Twitter::getInstance();
        
        $twitterForm = $twitter->form();
        
        if ($request->isPost()) {
            if ($twitterForm->isValid($request->getPost())) {
                $msg = $twitterForm->getValue('tweet');
                $response = $twitter->tweet($msg);
                
                $this->view->tweetResponse = $response;
            } else {
                $errors = $twitterForm->getErrors();
                if (in_array("stringLengthTooLong", $errors['tweet'])) {
                    $this->view->tweetResponse = array("error" => "msg_too_long");
                }
            }
        } else {
        }
        
        if (! $this->_request->isXmlHttpRequest()) {
            $this->_redirect($router->assemble($params, "sharepage_1stpage"), array("exit"));
        } else {
            $this->_helper->layout->disableLayout();
        }
    }
}