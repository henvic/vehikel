<?php

class Ml_View_Helper_Picture extends Zend_View_Helper_Abstract
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

    public function picture($picture = null, $options = "")
    {
        if (is_null($picture)) {
            $placeholder = $this->_picture->getPlaceholder();
            $src = $this->_picture->getImageLink($placeholder, $options);
        } else {
            $crop = $this->_picture->getCropOptions($picture["options"]);

            $src = $this->_picture->getImageLink($picture["picture_id"], $crop . $options);
        }

        $img = '<img src="' . $this->view->escape($src) . '" ' . 'alt="picture" />';

        return $img;
    }
}
