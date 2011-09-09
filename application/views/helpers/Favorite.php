<?php
class My_View_Helper_favorite extends Zend_View_Helper_Abstract
{
    public function favorite ()
    {
        return $this->view->render('shares/favorite.phtml');
    }
}
