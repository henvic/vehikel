<?php

class Ml_View_Helper_PostBreadcrumb extends Zend_View_Helper_Abstract
{
    /**
     * @param array $paths array of arrays with id, name and link keys, the least one being the active
     * @return string ul breadcrumb
     */
    public function PostBreadCrumb($paths)
    {
        $content = '<small class="post-breadcrumb">';

        $breadCrumbSize = count($paths);

        $content .= '<span class="hidden-phone"><a href="' . $this->view->url(array(), "search") . '?q=*">Carros, motos e outros</a> &gt; </span>';

        for ($pos = 0; $pos < $breadCrumbSize; $pos++) {
            $path = $paths[$pos];

            if ($pos == $breadCrumbSize - 1) {
                $content .= '<span id="' . $this->view->escape($path["id"]) . '" class="active">' .
                    $this->view->escape($path["name"]) . '</span>';
            } else {

                $content .= '<span id="' . $this->view->escape($path["id"]) . '"><a href="' .
                    $path["link"] .
                    '">' . $this->view->escape($path["name"]) .
                    '</a> <span class="divider">&gt;</span> </span>'
                ;
            }
        }

        $content .= '</small>';

        return $content;
    }
}
