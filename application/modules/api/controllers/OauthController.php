<?php

class OauthController extends Zend_Controller_Action
{
    public function accesstokenAction()
    {
        $server = new OAuthServer();
        $server->accessToken();
        exit;
    }
    
    public function requesttokenAction()
    {
        $server = new OAuthServer();
        $token = $server->requestToken();
        exit;
    }
}
