<?php

class Ml_View_Helper_PostBreadcrumb extends Zend_View_Helper_Abstract
{
    /**
     * @param array $paths array of arrays with id, name and link keys, the least one being the active
     * @param bool $linksActive whether the links are enabled or not
     * @return string ul breadcrumb
     */
    public function PostBreadCrumb($paths, $linksActive)
    {
        $isMuted = '';

        if (! $linksActive) {
            $isMuted = ' muted';
        }

        $content = '<small class="post-breadcrumb' . $isMuted . '">';

        $breadCrumbSize = count($paths);

        $content .= '<span class="hidden-phone">';

        if ($linksActive) {
            $content .= '<a href="' . $this->view->url(array(), "search") . '?q=*">';
        }

        $content .= 'Carros, motos e outros';

        if ($linksActive) {
            $content .= '</a>';
        }

        $content .= ' &gt; </span>';

        for ($pos = 0; $pos < $breadCrumbSize; $pos++) {
            $path = $paths[$pos];

            if ($pos == $breadCrumbSize - 1) {
                $content .= '<span id="' . $this->view->escape($path["id"]) . '" class="active">' .
                    $this->view->escape($path["name"]) . '</span>';
            } else {

                $content .= '<span id="' . $this->view->escape($path["id"]) . '">';

                if ($linksActive) {
                    $content .= '<a href="' . $path["link"] . '">';
                }

                $content .= $this->view->escape($path["name"]);

                if ($linksActive) {
                    $content .= '</a>';
                }

                $content .= ' <span class="divider">&gt;</span> </span>'
                ;
            }
        }

        $content .= '</small>';

        return $content;
    }
}
