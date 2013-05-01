<?php
class Ml_Form_PicturePostDelete extends Zend_Form
{
    public function init()
    {
        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/',
            Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/',
            Zend_Form_Element::FILTER);

        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setMethod('post');

        $pictureElement = new Zend_Form_Element_Text('picture_id');

        $pictureElement
            ->setRequired(true)
            ->setLabel("Picture ID")
            ->addValidator(new Ml_Validate_StringLength(["min" => 1, "max" => 64]))
            ->addValidator(new Zend_Validate_Regex("/^[\w\-]+$/"))
        ;

        $this->addElement($pictureElement);

        $this->addElement(Ml_Model_MagicCookies::formElement());
    }
}
