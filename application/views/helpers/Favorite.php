<?php
class Ml_View_Helper_Favorite extends Zend_View_Helper_Abstract
{
    public function favorite ()
    {
        return $this->view->render('shares/favorite.phtml');
    }
}
