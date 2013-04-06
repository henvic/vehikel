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

    public function picture($id, $secret, $format = "medium.jpg", $alt = 'picture', $width = false, $height = false)
    {
        $pictureLink = $this->_picture->getImageLink($id, $secret, $format);

        $img = '<img src="' .
            $this->view->escape($pictureLink) .
            '" alt="' . $this->view->escape($alt) . '"' .
            ' width="' . $this->view->escape($width) . '"' .
            ' height="' . $this->view->escape($height) . '"' .
            ' />';

        return $img;
    }
}
