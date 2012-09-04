<?php
class LogoutController extends Ml_Controller_Action
{
    public function indexAction()
    {
        $credential =  $this->_sc->get("credential");
        /** @var $credential \Ml_Model_Credential() */

        $logger =  $this->_sc->get("logger");
        /** @var $logger \Ml_Logger() */

        $session =  $this->_sc->get("session");
        /** @var $session \Ml_Session() */

        if (! $this->_auth->hasIdentity()) {
            $this->_redirect($this->_router->assemble(array(), "index"), array("exit"));
        }

        if ($this->_registry->isRegistered("signedUserInfo")) {
            $signedUserInfo = $this->_registry->get("signedUserInfo");
        }

        $form = $this->_sc->get("logoutForm");
        /** @var $form \Ml_Form_Logout() */

        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {

            ignore_user_abort(true);

            $unfilteredValues = $form->getUnfilteredValues();

            if (isset($unfilteredValues['remote_signout'])) {
                $session->remoteLogout();
                $logger->log(array("action" => "remote_logout_request"));
                $this->view->remoteLogoutDone = true;
            } else {
                $session->logout();
                $logger->log(array("action" => "logout_request"));
                $this->_redirect($this->_router->assemble(array(), "index"), array("exit"));
            }
        }

        $recentActivity = $session->getRecentActivity($signedUserInfo['id']);

        $this->view->logoutForm = $form;
        $this->view->recentActivity = $recentActivity;
    }
}