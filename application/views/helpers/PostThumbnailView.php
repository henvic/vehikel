<?php

class Ml_View_Helper_PostThumbnailView extends Zend_View_Helper_Abstract
{
    public function postThumbnailView($posts, $userInfo)
    {
        $content = "";

        $content .= '<ul class="thumbnails">';

        foreach ($posts as $post) {
            $escapedPostLink = $this->view->url(
                array("username" => $userInfo['username'], "post_id" => $post['id']), "user_post"
            );

            $picture = $post["pictures"][0];
            $price = new Zend_Currency(array("symbol" => "R$&nbsp;"), "pt_BR");
            $price->setValue($this->view->escape($post["price"] / 100));

            $content .= '<li class="span3">'
                . '<a href="' . $escapedPostLink . '" class="thumbnail vehicle-thumbnail">';

            $content .= $this->view->picture($userInfo["id"] . "-p-" . $picture["id"], $picture["secret"], "small.jpg");

            $content .= '<p>';

            $content .= $this->view->escape($post["name"])
                . '<br />'
                . '<span class="vehicle-listing-price">' . $price .  '</span><br />'
                . '<span class="vehicle-listing-details">';

            if ($post["build_year"] == $post["model_year"]) {
                $content .= $this->view->escape($post["model_year"]);
            } else {
                $content .= $this->view->escape($post["build_year"] . " / " . $post["model_year"]);
            }

            $content .= ' ' . '<span class="muted">|</span> ' . $this->view->escape($post["km"]) . ' km</span><br />';

            if ($post["armor"]) {
                $content .= '<span class="label label-important">veículo blindado</span> ';
            }

            if ($post["transmission"] == 'automatic') {
                $content .= '<span class="label label-info">câmbio automático</span> ';
            }

            if ($post["traction"] == '4x4') {
                $content .= '<span class="label label-warning">4x4</span> ';
            }

            if ($post["build_year"] <= date("Y") - 30) {
                $content .= '<span class="label label-inverse">antiguidade</span> ';
            }

            $content .= '</p></a></li>';
        }

        $content .= '</ul>';

        return $content;
    }
}
