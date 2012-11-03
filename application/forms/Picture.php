<?php

class Ml_Form_Picture extends Twitter_Bootstrap_Form_Horizontal
{
    public $_max_picture_size = 2097152; //2048 * 1024

    public function init()
    {
        $this->setAttrib('enctype', 'multipart/form-data');

        $this->setMethod('post');
        $this->setAction($this->getView()->url(array(), 'account_picture'));

        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/', 
        Zend_Form_Element::FILTER);

        $file = new Zend_Form_Element_File('Image');
        $file->setLabel('Escolha uma imagem:');
        $file->addValidator('Count', false, 1);
        $file->addValidator('Size', false, array('max' => '1MB'));
        $file->setMaxFileSize($this->_max_picture_size);
        $file->addValidator('Extension', false, 'jpg,png,gif');
        $file->setRequired(false);
        $file->setOptions(Array('ignoreNoFile' => true));
        $this->addElement($file, 'Image');
        
        $this->addElement('submit', 'submit', array(
            'label'    => 'Enviar',
            'class'    => 'btn btn-primary',
        ));
        
        $this->addElement('submit', 'delete', array(
            'label'    => 'Remover atual',
            'class'    => 'btn btn-danger',
        ));

        $this->addElement(Ml_Model_MagicCookies::formElement());

        $this->addDisplayGroup(
            array('submit', 'reset'),
            'actions',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => array('Actions')
            )
        );

        $this->setAttrib('class', 'form-stacked');
    }
}
