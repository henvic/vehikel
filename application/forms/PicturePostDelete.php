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

        $this->addElement('text', 'picture_id', array(
            'label'      => 'Picture Id',
            'required'   => true,
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(1, 20)),
                array('validator' => 'Digits')
            )
        ));

        $this->addElement(Ml_Model_MagicCookies::formElement());
    }
}
