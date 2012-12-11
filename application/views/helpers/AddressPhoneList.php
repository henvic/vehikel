<?php

class Ml_View_Helper_AddressPhoneList extends Zend_View_Helper_Abstract
{
    public function addressPhoneList($phoneList)
    {
        $content = '';

        foreach($phoneList as $phone) {
            $formattedBrPhone = sprintf("%s %s-%s",
                mb_substr($phone["tel"], 3, 2),
                mb_substr($phone["tel"], 5, 4),
                mb_substr($phone["tel"], 9)
            );

            $content .= '<li>'
                . '<a class="tel" href="tel:'
                . $this->view->escape($phone["tel"])
                . '">☎ '
                . $this->view->escape($formattedBrPhone);

            if ($phone["name"]) {
                $content .= ' - ' . $this->view->escape($phone["name"]);
            }

            $content .= '</a></li>';
        }

        $content .= '';

        return $content;
    }
}
