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

    public function pictureLink($prefix, $secret, $format = "medium.jpg")
    {
        return $this->_picture->getImageLink($prefix, $secret, $format);
    }
}
