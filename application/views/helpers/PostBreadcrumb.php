<?php

class Ml_View_Helper_PostBreadcrumb extends Zend_View_Helper_Abstract
{
    /**
     * @param array $paths array of arrays with id, name and link keys, the least one being the active
     * @return string ul breadcrumb
     */
    public function PostBreadCrumb($paths)
    {
        $content = '<ul class="post-breadcrumb breadcrumb">';

        $breadCrumbSize = count($paths);

        for ($pos = 0; $pos < $breadCrumbSize; $pos++) {
            $path = $paths[$pos];

            if ($pos == $breadCrumbSize - 1) {
                $content .= '<li id="' . $this->view->escape($path["id"]) . '" class="active">' .
                    $this->view->escape($path["name"]) . '</li>';
            } else {

                $content .= '<li id="' . $this->view->escape($path["id"]) . '"><a href="' .
                    $path["link"] .
                    '">' . $this->view->escape($path["name"]) .
                    '</a> <span class="divider">&gt;</span></li>'
                ;
            }
        }

        $content .= '</ul>';

        return $content;
    }
}
