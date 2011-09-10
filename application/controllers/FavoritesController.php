<?php

/**
 * Favorites Controller
 *
 * (see shares controller header notes)
 *
 * @copyright  2009 Henrique Vicente
 * @version    $Id:$
 * @since      File available since Release 0.1
 */


class FavoritesController extends Zend_Controller_Action
{
    public function init()
    {
        $action = $this->getRequest()->getActionName();
        
        if ($action == "share" || $action == "user") {
            $this->_helper->loadResource->pseudoshareSetUp();
        }
    }
    
    public function switchAction()
    {
        $auth = Zend_Auth::getInstance();
        $registry = Zend_Registry::getInstance();
        
        $router = $this->getFrontController()->getRouter();
        
        $userInfo = $registry->get("userInfo");
        $shareInfo = $registry->get("shareInfo");
        
        $request = $this->getRequest();
        $params = $request->getParams();
        
        $favorites = Ml_Favorites::getInstance();
        if ($auth->hasIdentity() && $auth->getIdentity() != $userInfo['id']) {
            $favoriteForm = $favorites->_form();
            if ($request->isPost() && $favoriteForm->isValid($request->getPost())) {
                if (array_key_exists("unfavorite", $params)) {
                    $favorites->delete($favorites->getAdapter()
                        ->quoteInto("uid = ?", $auth->getIdentity()) .
                     $favorites->getAdapter()
                        ->quoteInto(" AND share = ?", $shareInfo['id']));
                } else if (array_key_exists("favorite", $params)) {
                    $favorites->getAdapter()->query("INSERT IGNORE INTO `" .
                        $favorites->getTableName() .
                         "` (uid, share, byUid) SELECT ?, ?, ? FROM DUAL WHERE not exists (select * from `ignore` where ignore.uid = ? AND ignore.ignore = ?)", 
                        array($auth->getIdentity(), $shareInfo['id'], 
                        $shareInfo['byUid'], $shareInfo['byUid'], 
                        $auth->getIdentity()));
                }
                $done = true;
            }
        } else {
            $done = true;
        }
        if ($this->_request->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
        } else if (isset($done)) {
            $this->_redirect($router->assemble($params, "sharepage_1stpage"), array("exit"));
        } else {
            if (array_key_exists("unfavorite", $params)) {
                $this->view->favoriteDel = true;
            }
            $this->view->favoriteForm = $favoriteForm;
        }
    }
    
    public function shareAction() {
        $registry = Zend_Registry::getInstance();
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $favorites = Ml_Favorites::getInstance();
        $people = Ml_People::getInstance();
        
        $request = $this->getRequest();
        
        $userInfo = $registry->get('userInfo');
        $shareInfo = $registry->get('shareInfo');
        
        $page = $request->getUserParam("page");
        
        $paginator = $favorites->getSharePage($shareInfo['id'], 25, $page);
        
        //Test if there is enough pages or not
        if ((! $paginator->count() && $page != 1) ||
         $paginator->getCurrentPageNumber() != $page) {
            $this->_redirect($router->assemble(array("username" =>
            $userInfo['alias'], "share_id" => $shareInfo['id']),
            "favorites_1stpage"), array("exit"));
        }
        
        $this->view->paginator = $paginator;
    }
    
    public function userAction() {
        $registry = Zend_Registry::getInstance();
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $favorites = new Ml_Favorites();
        $share = Ml_Share::getInstance();
        $people = Ml_People::getInstance();
        
        $request = $this->getRequest();
        
        $userInfo = $registry->get('userInfo');
        
        $page = $request->getUserParam("page");
        
        $paginator = $favorites->getUserPage($userInfo['id'], 25, $page);
        
        //Test if there is enough pages or not
        if ((! $paginator->count() && $page != 1) ||
         $paginator->getCurrentPageNumber() != $page) {
            $this->_redirect($router->assemble(array("username" =>
            $userInfo['alias']), "userfav_1stpage"), array("exit"));
        }
        
        $this->view->paginator = $paginator;
    }
}