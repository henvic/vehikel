<?php
class Ml_Form_PicturePostEdit extends Zend_Form
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

        $this->addElement('text', 'x', array(
            'label' => 'x',
            'required' => true,
            'validators' => array(array('validator' => 'Digits'))
        ));

        $this->addElement('text', 'y', array(
            'label' => 'y',
            'required' => true,
            'validators' => array(array('validator' => 'Digits'))
        ));

        $this->addElement('text', 'x2', array(
            'label' => 'x2',
            'required' => true,
            'validators' => array(array('validator' => 'Digits'))
        ));

        $this->addElement('text', 'y2', array(
            'label' => 'y2',
            'required' => true,
            'validators' => array(array('validator' => 'Digits'))
        ));

        $this->addElement(Ml_Model_MagicCookies::formElement());
    }
}
