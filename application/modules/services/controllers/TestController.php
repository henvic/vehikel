<?php

class TestController extends Zend_Controller_Action 
{	
	public function addapiserverAction()
	{
		$user_id = $Service->getInput("User ID");
		$consumer_key = $Service->getInput("Consumer key");
		$consumer_secret = $Service->getInput("Consumer secret");
		
        $this->_helper->loadOauthstore->setinstance();
		
        $store = OAuthStore::instance();
        
		// The server description
		$server = array(
		    'consumer_key' => $consumer_key,
		    'consumer_secret' => $consumer_secret,
		    'server_uri' => 'http://mercury/',
		    'signature_methods' => array('HMAC-SHA1', 'PLAINTEXT'),
		    'request_token_uri' => 'http://mercury/oauth/request_token',
		    'authorize_uri' => 'http://127.0.0.2/api/authorize',
		    'access_token_uri' => 'http://mercury/oauth/access_token'
		);
		
		// Save the server in the the OAuthStore
		$consumer_key = $store->updateServer($server, $user_id);
		
		echo "Api server added!\n";
	}
	
	public function delapiserverAction()
	{
		$Service = new ML_Service();
		
		$user_id = $Service->getInput("User ID");
		$consumer_key = $Service->getInput("Consumer key");
		
		$this->_helper->loadOauthstore->setinstance();
		
        $store = OAuthStore::instance();
        
		$store->deleteServer($consumer_key, $user_id);
	}
	
	public function listapiserversAction()
	{
		$Service = new ML_Service();
		
		$user_id = $Service->getInput("User ID");
		
		$this->_helper->loadOauthstore->setinstance();
		
        $store = OAuthStore::instance();
        
		$servers = $store->listServers(null, $user_id);
		
		print_r($servers);
	}
	
	public function requestapiauthAction()
	{
		$Service = new ML_Service();
		
		$user_id = $Service->getInput("User ID");
		
		$consumer_key = $Service->getInput("Consumer key");
		
		$this->_helper->loadOauthstore->setinstance();
		
		require EXTERNAL_LIBRARY_PATH . '/oauth-php/library/OAuthRequester.php';
		$token = OAuthRequester::requestRequestToken($consumer_key, $user_id);
		
		// Callback to our (consumer) site, will be called when the user finished the authorization at the server
		$callback_uri = 'http://example.com/callback?consumer_key='.rawurlencode($consumer_key).'&usr_id='.intval($user_id);
		
		// Now redirect to the autorization uri and get us authorized
		if (!empty($token['authorize_uri']))
		{
		    // Redirect to the server, add a callback to our server
		    if (strpos($token['authorize_uri'], '?'))
		    {
		        $uri = $token['authorize_uri'] . '&';
		    }
		    else
		    {
		        $uri = $token['authorize_uri'] . '?';
		    }
		    $uri .= 'oauth_token='.rawurlencode($token['token']).'&oauth_callback='.rawurlencode($callback_uri);
		}
		else
		{
		    // No authorization uri, assume we are authorized, exchange request token for access token
		   $uri = $callback_uri . '&oauth_token='.rawurlencode($token['token']);
		}
		
		echo $uri;
		//exit();
	}
	
	public function exchangeapitokenAction()
	{
		$Service = new ML_Service();
		
		$this->_helper->loadOauthstore->setinstance();
		
		$user_id = $Service->getInput("User ID");
		
		$consumer_key = $Service->getInput("Consumer key");
		
		$oauth_token = $Service->getInput("Oauth token");
		
		require EXTERNAL_LIBRARY_PATH . '/oauth-php/library/OAuthRequester.php';
		
		try
		{
		    OAuthRequester::requestAccessToken($consumer_key, $oauth_token, $user_id);
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
		$Service = new ML_Service();
		
		$this->_helper->loadOauthstore->setinstance();
		
		require EXTERNAL_LIBRARY_PATH . '/oauth-php/library/OAuthRequester.php';
		
		$user_id = $Service->getInput("User ID");
		
		$request_uri = $Service->getInput("Request URI");
		
		$http_method = $Service->getInput("HTTP Method");
		
		// Parameters, appended to the request depending on the request method.
		// Will become the POST body or the GET query string.
		$num_of_params = $Service->getInput("Number of params");
		$params = array();
		for($n = 0; $n < $num_of_params; $n++)
		{
			$param = $Service->getInput("Param");
			$value = $Service->getInput("Value");
			
			$params[$param] = $value;
		}
		
		
		// Obtain a request object for the request we want to make
		$req = new OAuthRequester($request_uri, $http_method, $params);
		
		try {
			// Sign the request, perform a curl request and return the results, throws OAuthException exception on an error
			$result = $req->doRequest($user_id);
			// $result is an array of the form: array ('code'=>int, 'headers'=>array(), 'body'=>string)
			print_r($result);
		} catch(Exception $e)
		{
			print_r($e->getMessage());
		}
		
	}
	
	public function testingAction()
	{
		$Favorites = ML_Favorites::getInstance();
		
		$Favorites->getUserPage(1, 1,1);
	}
}