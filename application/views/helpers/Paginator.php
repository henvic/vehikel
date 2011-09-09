<?php
/**
 * Flickr-like paginator clone
 * @author henrique
 *
 */
class My_View_Helper_paginator extends Zend_View_Helper_Abstract
{
    public function paginator ($activePage, $total)
    {
        $pages = array();
        if ($total <= 12) {
            for ($page = 1; $page <= $total; $page ++) {
                $pages[] = $page;
            }
        } else if ($activePage >= $total - 8) {
            for ($page = 1; $page <= 2; $page ++) {
                $pages[] = $page;
            }
            $leftLim = ($total - $activePage <= 2) ? - $total +
            $activePage + 6 : 3;
            
            for ($page = $activePage - $leftLim; $page <= $total; $page ++) {
                if ($page > 0 && $page <= $total) {
                    $pages[] = $page;
                }
            }
        } else if ($activePage <= 8) {
            $rightLim = ($activePage <= 2) ? 7 - $activePage : 3;
            for ($page = 1; $page <= $activePage + $rightLim; $page ++) {
                $pages[] = $page;
            }
            for ($page = $total - 1; $page <= $total; $page ++) {
                $pages[] = $page;
            }
        } else {
            for ($page = 1; $page <= 2; $page ++) {
                $pages[] = $page;
            }
            for ($page = $activePage - 3; $page <= $activePage + 3; $page ++) {
                $pages[] = $page;
            }
            for ($page = $total - 1; $page <= $total; $page ++) {
                $pages[] = $page;
            }
        }
        return $pages;
    }
}
