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
    public function avatar($userInfo)
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        $picture =  $registry->get("sc")->get("picture");
        /** @var $picture \Ml_Model_Picture() */

        $pictureInfo = $userInfo["avatarInfo"];

        if ($pictureInfo) {
            $pictureLink = $picture->getImageLink($pictureInfo["prefix"], $pictureInfo["secret"], "square.jpg");
        } else {
            $pictureLink = $config["cdn"] . "images/happy-face-s.png";
        }
        return '<img src="' . $this->view->escape($pictureLink) . '" width="20" height="20" />';
     }
}
