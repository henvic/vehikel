<?php

class TestController extends Zend_Controller_Action
{
    public function addapiserverAction()
    {
        $service = new Ml_Model_Service();
        
        $userId = $service->getInput("User ID");
        $consumerKey = $service->getInput("Consumer key");
        $consumerSecret = $service->getInput("Consumer secret");
        
        $this->_helper->loadOauthstore->setinstance();
        
        $store = OAuthStore::instance();
        
        // The server description
        $server = array(
            'consumer_key' => $consumerKey,
            'consumer_secret' => $consumerSecret,
            'server_uri' => 'http://mercury/',
            'signature_methods' => array('HMAC-SHA1', 'PLAINTEXT'),
            'request_token_uri' => 'http://mercury/oauth/request_token',
            'authorize_uri' => 'http://127.0.0.2/api/authorize',
            'access_token_uri' => 'http://mercury/oauth/access_token'
        );
        
        // Save the server in the the OAuthStore
        $consumerKey = $store->updateServer($server, $userId);
        
        echo "Api server added!\n";
    }
    
    public function delapiserverAction()
    {
        $service = new Ml_Model_Service();
        
        $userId = $service->getInput("User ID");
        $consumerKey = $service->getInput("Consumer key");
        
        $this->_helper->loadOauthstore->setinstance();
        
        $store = OAuthStore::instance();
        
        $store->deleteServer($consumerKey, $userId);
    }
    
    public function listapiserversAction()
    {
        $service = new Ml_Model_Service();
        
        $userId = $service->getInput("User ID");
        
        $this->_helper->loadOauthstore->setinstance();
        
        $store = OAuthStore::instance();
        
        $servers = $store->listServers(null, $userId);
        
        print_r($servers);
    }
    
    public function requestapiauthAction()
    {
        $service = new Ml_Model_Service();
        
        $userId = $service->getInput("User ID");
        
        $consumerKey = $service->getInput("Consumer key");
        
        $this->_helper->loadOauthstore->setinstance();
        
        require EXTERNAL_LIBRARY_PATH . '/oauth-php/library/OAuthRequester.php';
        $token = OAuthRequester::requestRequestToken($consumerKey, $userId);
        
        // Callback to our (consumer) site
        //it's called when the user finished the authorization at the server
        $callbackUri = 'http://example.com/callback?consumer_key=' .
         rawurlencode($consumerKey) . '&usr_id=' . intval($userId);
        
        // Now redirect to the autorization uri and get us authorized
        if (!empty($token['authorize_uri'])) {
            // Redirect to the server, add a callback to our server
            if (strpos($token['authorize_uri'], '?')) {
                $uri = $token['authorize_uri'] . '&';
            } else {
                $uri = $token['authorize_uri'] . '?';
            }
            $uri .= 'oauth_token=' . rawurlencode($token['token']) .
             '&oauth_callback=' . rawurlencode($callbackUri);
        } else {
            // No authorization uri
            //assume we are authorized
            //exchange request token for access token
           $uri = $callbackUri . '&oauth_token='.rawurlencode($token['token']);
        }
        
        echo $uri;
        //exit();
    }
    
    public function exchangeapitokenAction()
    {
        $service = new Ml_Model_Service();
        
        $this->_helper->loadOauthstore->setinstance();
        
        $userId = $service->getInput("User ID");
        
        $consumerKey = $service->getInput("Consumer key");
        
        $oauthToken = $service->getInput("Oauth token");
        
        require EXTERNAL_LIBRARY_PATH . '/oauth-php/library/OAuthRequester.php';
        
        try
        {
            OAuthRequester::requestAccessToken($consumerKey, $oauthToken, $userId);
            echo "Request token exchanged for access token.\n";
        }
        catch (OAuthException $e)
        {
        // Something wrong with the oauth_token.
           // Could be:
            // 1. Was already ok
            // 2. We were not authorized
            throw $e;
        }
    }
    
    public function apisignedrequestAction()
    {
        $service = new Ml_Model_Service();
        
        $this->_helper->loadOauthstore->setinstance();
        
        require EXTERNAL_LIBRARY_PATH . '/oauth-php/library/OAuthRequester.php';
        
        $userId = $service->getInput("User ID");
        
        $requestUri = $service->getInput("Request URI");
        
        $httpMethod = $service->getInput("HTTP Method");
        
        // Parameters, appended to the request depending on the request method.
        // Will become the POST body or the GET query string.
        $numOfParams = $service->getInput("Number of params");
        $params = array();
        for ($n = 0; $n < $numOfParams; $n ++) {
            $param = $service->getInput("Param");
            $value = $service->getInput("Value");
            
            $params[$param] = $value;
        }
        
        
        // Obtain a request object for the request we want to make
        $req = new OAuthRequester($requestUri, $httpMethod, $params);
        
        try {
            // Sign the request, perform a curl request and return the results,
            //throws OAuthException exception on an error
            $result = $req->doRequest($userId);
            //$result is an array with the content:
            //array ('code'=>int, 'headers'=>array(), 'body'=>string)
            print_r($result);
        } catch(Exception $e)
        {
            print_r($e->getMessage());
        }
        
    }
    
    public function testingAction()
    {
        $favorites = Ml_Model_Favorites::getInstance();
        
        $favorites->getUserPage(1, 1, 1);
    }
}