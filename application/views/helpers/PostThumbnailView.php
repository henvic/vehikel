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

            $price = new Zend_Currency(array("symbol" => "R$&nbsp;"), "pt_BR");
            $price->setValue($this->view->escape($post["price"] / 100));

            $content .= '<li class="span3">'
                . '<a href="' . $escapedPostLink . '" class="thumbnail post-thumbnail">';

            if (is_array($post["pictures"][0])) {
                $picture = $post["pictures"][0];
                $content .= $this->view->picture($picture["id"], $picture["secret"], "small.jpg");
            } else {
                $content .= '<img src="' . $this->view->staticVersion("/images/chevrolet-impala-icon-small.png") . '" alt="picture placeholder">';
            }

            $content .= '<p>';

            $content .= $this->view->escape($post["make"] . " " . $post["model"] . " " .
                $post["engine"] . " " . $post["name"])
                . '<br />'
                . '<span class="post-listing-price">' . $price .  '</span><br />'
                . '<span class="post-listing-details">';

            $content .= $this->view->escape($post["model_year"]);

            if ($post["km"]) {
                $content .= ' ' . '<span class="muted">|</span> ' . $this->view->escape($post["km"]) . ' km';
            }

            $content .= '</span><br />';

            if ($post["armor"]) {
                $content .= '<span class="label label-important">veículo blindado</span> ';
            }

            if ($post["transmission"] == 'automatic') {
                $content .= '<span class="label label-info">câmbio automático</span> ';
            }

            if ($post["traction"] == '4x4') {
                $content .= '<span class="label label-warning">4x4</span> ';
            }

            if ($post["model_year"] <= date("Y") - 30) {
                $content .= '<span class="label label-inverse">colecionador</span> ';
            }

            $content .= '</p></a></li>';
        }

        $content .= '</ul>';

        return $content;
    }
}
