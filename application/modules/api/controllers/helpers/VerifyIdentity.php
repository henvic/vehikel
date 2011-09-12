<?php
class Zend_Controller_Action_Helper_VerifyIdentity extends
                Zend_Controller_Action_Helper_Abstract
{
    public function direct()
    {
        if (OAuthRequestVerifier::requestIsSigned()) {
            try {
                
                $req = new OAuthRequestVerifier();
                $authUid = $req->verify();
                
                if ($authUid) {
                    $registry = Zend_Registry::getInstance();
                    
                    $people = Ml_Model_People::getInstance();
                    $authedUserInfo = $people->getById($authUid);
                    
                    $registry->set("authedUserInfo", $authedUserInfo);
                }
            } catch(OAuthException $e)
            {
                //If user authentication fails
                header('HTTP/1.1 401 Unauthorized');
                header('WWW-Authenticate: OAuth realm=""');
                header('Content-Type: text/plain; charset=utf8');
                
                throw $e;
            }
        }
    }
}
