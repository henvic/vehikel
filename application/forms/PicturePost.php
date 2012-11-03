<?php
class Ml_Form_PicturePost extends Zend_Form
{
    public function init()
    {
        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/',
            Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/',
            Zend_Form_Element::FILTER);

        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setMethod('post');

        $file = new Zend_Form_Element_File('Filedata');
        $file->addValidator(new Zend_Validate_File_Size(["max" => "5MB"]), false);
        $file->addValidator(new Zend_Validate_File_Count(["min" => 1, "max" => 1]), false);
        $this->addElement($file);

        $this->addElement(Ml_Model_MagicCookies::formElement());

        $this->setAttrib('class', 'form-stacked');
    }
}
