<?php
/**
 * Flickr-like paginator clone
 * @author henrique
 *
 */
class Ml_View_Helper_PaginatorLink extends Zend_View_Helper_Abstract
{
    const FIRST_PAGE_ROUTE_SUFFIX = "_1stpage";
    public function paginatorLink ($page)
    {
        $frontController = Zend_Controller_Front::getInstance();
        $userParams = $frontController->getRequest()->getUserParams();
        
        $routeName = $frontController->getRouter()->getCurrentRouteName();
        
        if ($page == null) {
            return null;
        }
        
        if ($page == 1 && mb_substr($routeName, -8) != self::FIRST_PAGE_ROUTE_SUFFIX) {
            $routeName = $routeName . self::FIRST_PAGE_ROUTE_SUFFIX;
        } else if ($userParams["page"] == 1 && $page > 1 && mb_substr($routeName, -8) == self::FIRST_PAGE_ROUTE_SUFFIX) {
            $routeName = mb_substr($routeName, 0, -8);
        }
        
        $userParams["page"] = $page;
        
        if ($page != 1 && isset($userParams["paginator_section_cont"])) {
            $append = "#" . $userParams["paginator_section_cont"];
        } else {
            $append = null;
        }
        
        return $this->view->url($userParams, $routeName) . $append;
    }
}
