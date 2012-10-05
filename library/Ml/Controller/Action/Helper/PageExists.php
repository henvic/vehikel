<?php
class Ml_Controller_Action_Helper_PageExists extends Zend_Controller_Action_Helper_Abstract
{
    public function direct(Zend_Paginator $paginator)
    {
        $page = $this->getRequest()->getUserParam("page");

        if ($paginator->getCurrentPageNumber() != $page) {
            return false;
        }

        if (! $paginator->count() && $page != 1) {
            return false;
        }

        return true;
    }
}