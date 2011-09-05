<?php
class Form_Picture extends Zend_Form
{    
    public function init()
    {
        $this->setAttrib('enctype', 'multipart/form-data');
        
        $this->setMethod('post');
        $this->addElementPrefixPath('MLValidator', 'ML/Validators/', Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('MLFilter', 'ML/Filters/', Zend_Form_Element::FILTER);
        
        $File = new Zend_Form_Element_File('Image');
        $File->setLabel('Choose a picture:');
        $File->addValidator('Count', false, 1);
        $File->addValidator('Size', false, array('max' => '1MB'));
        $File->setMaxFileSize(2048*1024);
        $File->addValidator('Extension', false, 'jpg,png,gif');
        $File->setRequired(false);
        $File->setOptions(Array('ignoreNoFile' => true));
        $this->addElement($File, 'Image');
        
        $this->addElement('submit', 'submit', array(
            'label'    => 'Submit!',
        ));
        
        $this->addElement('submit', 'delete', array(
            'label'    => 'Delete current!',
        ));
        
        $this->getElement("delete")->setAttrib("class", "likelink");
        
        $this->addElement(ML_MagicCookies::formElement());
    }
}
