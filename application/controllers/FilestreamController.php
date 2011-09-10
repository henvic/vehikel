<?php

/**
 * Filestream Controller
 *
 * All actions in this controller demand an associated username.
 * And some actions do demand the share_id user param
 *
 * @copyright  2008 Henrique Vicente
 * @version    $Id:$
 * @since      File available since Release 0.1
 */

class FilestreamController extends Zend_Controller_Action
{
    public function init()
    {    
        $this->_helper->loadResource->pseudoshareSetUp();
    }
    
    public function filestreamAction()
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get('config');
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $request = $this->getRequest();
        
        $share = Ml_Share::getInstance();
        
        $userInfo = $registry->get('userInfo');
        
        $page = $request->getUserParam("page");
        
        $paginator = $share->getPages($userInfo['id'], $config['share']['perPage'], $page);
        
        //Test if there is enough pages or not
        if (((! $paginator->count() && $page != 1) && $page != 1) ||
         $paginator->getCurrentPageNumber() != $page) {
            $this->_redirect($router->assemble(array("username" =>
            $userInfo['alias']), "filestream_1stpage"), array("exit"));
        }
        
        $this->view->paginator = $paginator;
    }
}