<?php

/**
 * Avatar
 *
 * @author henrique
 * @todo refactory this class
 *
 */
class Ml_View_Helper_Avatar extends Zend_View_Helper_Abstract
{
    public function avatar($userInfo, $format = "square.jpg", $width = false, $height = false)
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        $picture =  $registry->get("sc")->get("picture");
        /** @var $picture \Ml_Model_Picture() */

        $pictureInfo = $userInfo["avatar_info"];

        if ($pictureInfo) {
            $pictureLink = $picture->getImageLink($pictureInfo["id"], $pictureInfo["secret"], $format);
        } else {
            $pictureLink = $config["cdn"] . "images/user-image-placeholder/" . $format;
        }
        $img = '<img src="' . $this->view->escape($pictureLink);

        $img .= '"';

        if ($width) {
            $img .= ' width="' . (int) $width . '"';
        }

        if ($height) {
            $img .= ' height="' . (int) $height . '"';
        }

        $img .= ' alt="" />';

        return $img;
     }
}
