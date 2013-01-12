<?php

class Ml_View_Helper_PostTableView extends Zend_View_Helper_Abstract
{
    public function postTableView($posts, $userInfo)
    {
        $content = "";

        $content .= '<table class="table table-striped table-hover">';

        foreach ($posts as $post) {
            $escapedPostLink = $this->view->url(
                array("username" => $userInfo['username'], "post_id" => $post['id']), "user_post"
            );

            $price = new Zend_Currency(array("symbol" => "R$&nbsp;"), "pt_BR");
            $price->setValue($this->view->escape($post["price"] / 100));

            $content .= '<tr>'
                . '<td class="span1">';

            if (is_array($post["pictures"][0])) {
                $picture = $post["pictures"][0];
                $content .= $this->view->picture($picture["id"], $picture["secret"], "square.jpg");
            } else {
                $content .= '<img src="' . $this->view->staticVersion("/images/chevrolet-impala-icon-square.png") . '" alt="picture placeholder">';
            }

            $content .= '</td>'
                . '<td>'
                . '<div class="span5">';

            $content .= '<a href="' . $escapedPostLink . '">' .
                $this->view->escape($post["make"] . " " . $post["model"] . " " .
                    $post["engine"] . " " . $post["name"]) . '</a><br />';

            $content .= $this->view->escape($post["model_year"]);

            if ($post["km"] != "") {
                $content .= '<span class="muted"> | </span>' . $this->view->escape($post["km"]) . ' km';
            }

            $content .= '</div>'
                . '<div class="span2 special-items">';


            $specialItems = [];

            if ($post["armor"]) {
                $specialItems[] = 'Blindado';
            }

            if ($post["transmission"] == 'automatic') {
                $specialItems[] = 'Automático';
            }

            if ($post["traction"] == '4x4') {
                $specialItems[] = '4x4';
            }

            $sizeOfSpecialItems = count($specialItems);

            for ($counter = 0; $counter < $sizeOfSpecialItems; $counter++)
            {
                $content .= $specialItems[$counter];

                if ($counter != $sizeOfSpecialItems - 1) {
                    $content .= '<br />';
                }
            }

            $content .= '</div>'
                . '</td>'
                . '<td class="post-listing-price">'
                . $price
                . '</td>'
                . '</tr>'
            ;
        }

        $content .= '</table>';

        return $content;
    }
}
