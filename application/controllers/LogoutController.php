<?php 
class LogoutController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $auth = Zend_Auth::getInstance();
        $registry = Zend_Registry::getInstance();
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $request = $this->getRequest();
        
        $params = $request->getParams();
        
        $credential = Ml_Model_Credential::getInstance();
        $session = Ml_Model_Session::getInstance();
        
        if (! $auth->hasIdentity()) {
            $this->_redirect($router->assemble(array(), "index"), array("exit"));
        }
        
        if ($registry->isRegistered("signedUserInfo")) {
            $signedUserInfo = $registry->get("signedUserInfo");
        }
        
        $form = $credential->logoutForm();
        
        if ($request->isPost() && $form->isValid($request->getPost())) {
            
            ignore_user_abort(true);
            
            $unfilteredValues = $form->getUnfilteredValues();
            
            if (isset($unfilteredValues['remote_signout'])) {
                $session->remoteLogout();
                $this->view->remoteLogoutDone = true;
            } else {
                $session->logout();
                $this->_redirect($router->assemble(array(), "index"), array("exit"));
            }
        }
        
        $recentActivity = $session->getRecentActivity($signedUserInfo['id']);
        
        $this->view->logoutForm = $form;
        $this->view->recentActivity = $recentActivity;
    }
}