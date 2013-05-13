<?php

class Ml_View_Helper_PictureLink extends Zend_View_Helper_Abstract
{
    protected $_registry;

    protected $_picture;

    public function __construct()
    {
        $this->_registry = $registry = Zend_Registry::getInstance();

        $picture =  $registry->get("sc")->get("picture");
        /** @var $picture \Ml_Model_Picture() */

        $this->_picture = $picture;
    }

    public function pictureLink($picture = null, $options = "")
    {
        $src = "";

        if (is_null($picture)) {
            $placeholder = $this->_picture->getPlaceholder();
            $src .= $this->_picture->getImageLink($placeholder, $options);
        } else {
            $crop = $this->_picture->getCropOptions($picture["options"]);

            $src .= $this->_picture->getImageLink($picture["picture_id"], $crop . $options);
        }

        return $src;
    }
}
