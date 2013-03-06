<?php

class Ml_Controller_Action_Helper_ViewStyle extends Zend_Controller_Action_Helper_Abstract
{
    public function direct()
    {
        $params = $this->getFrontController()->getRequest()->getParams();

        $postsViewStyleNamespace = new Zend_Session_Namespace("posts-view-style");

        if (isset($params["posts_view_style"])) {
            if ($params["posts_view_style"] === 'table') {
                $postsViewStyleNamespace->style = "table";
            } else {
                $postsViewStyleNamespace->style = "thumbnail";
            }
        } else if ($postsViewStyleNamespace->style === null) {
            $postsViewStyleNamespace->style = "thumbnail";
        }

        $viewRenderer = $this->getActionController()->getHelper("viewRenderer");

        $viewRenderer->view->addJsParam("postsViewStyle", $postsViewStyleNamespace->style);
        $viewRenderer->view->assign("postsViewStyle", $postsViewStyleNamespace->style);
    }
}