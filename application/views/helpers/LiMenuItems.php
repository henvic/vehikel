<?php
class Ml_View_Helper_LiMenuItems extends Zend_View_Helper_Abstract
{
    /**
     * 
     * Create li menu items (the active link's li has the active class)
     * @param array $items (each item is a array containing keys route, and name required; array params, escape name boolean and link id / rel are optionals)
     * @return string $partialMenuHtml the list elements
     * 
     */
    public function liMenuItems ($items)
    {
        $frontController = Zend_Controller_Front::getInstance();
        
        $userParams = $frontController->getRequest()->getUserParams();
        
        $router = $frontController->getRouter();
        $currentRouteName = $router->getCurrentRouteName();
        
        $partialMenuHtml = '';
        foreach ($items as $item) {
            if (! isset($item["params"])) {
                $item["params"] = array();
            }
            
            if ($currentRouteName != $item["route"]) {
                $different = true;
            } else if (empty($userParams)) {
                $different = false;
            } else {
                $different = false;
                foreach ($item["params"] as $param => $value) {
                    if (! isset($userParams[$param]) || $userParams[$param] != $value) {
                        $different = true;
                        break;
                    }
                }
            }
            
            if (! $different) {
                $each = '<li class="active">';
            } else {
                $each = '<li>';
            }
            
            $link = $router->assemble($item["params"], $item["route"]);
            
            $each .= '<a href="' . $link . '"';
            
            if (isset($item["id"])) {
                $each .= ' id="' . $this->view->escape($item["id"]) . '"';
            }
            
            if (isset($item["rel"])) {
                $each .= ' rel="' . $this->view->escape($item["rel"]) . '"';
            }
            
            $each .= '>';
            
            if (! isset($item["escape"]) || $item["escape"] == true) {
                $name = $this->view->escape($item["name"]);
            } else {
                $name = $item["name"];
            }
            
            $each .= $name . "</a></li>" . PHP_EOL;
            
            $partialMenuHtml .= $each;
        }
        
        return $partialMenuHtml;
    }
}