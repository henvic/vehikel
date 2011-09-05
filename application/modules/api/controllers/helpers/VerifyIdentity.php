<?php
class Zend_Controller_Action_Helper_VerifyIdentity extends
                Zend_Controller_Action_Helper_Abstract
{
    public function direct()
    {
        if(OAuthRequestVerifier::requestIsSigned())
        {
            try {
                $req = new OAuthRequestVerifier();
                $auth_uid = $req->verify();
                if($auth_uid)
                {
                    $People = ML_People::getInstance();
                    $authedUserInfo = $People->getById($auth_uid);
                    
                    Zend_Registry::getInstance()->set("authedUserInfo", $authedUserInfo);
                }
            } catch(OAuthException $e)//If user authentication fails
            {
                header('HTTP/1.1 401 Unauthorized');
                header('WWW-Authenticate: OAuth realm=""');
                header('Content-Type: text/plain; charset=utf8');
                
                throw $e;
            }
        }
    }
}
