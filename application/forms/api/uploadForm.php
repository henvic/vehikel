<?php
class Form_Upload extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->addElementPrefixPath('MLValidator', 'ML/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('MLFilter', 'ML/Filters/', 
        Zend_Form_Element::FILTER);
        
        $registry = Zend_Registry::getInstance();
        $authedUserInfo = $registry->get('authedUserInfo');
        $uploadStatus = $registry->get("uploadStatus");
        
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setMethod('post');
        
        $file = new Zend_Form_Element_File('file');
        $file->setLabel('Files:');
        $file->setRequired(true);
        $file->addValidator('Count', false, array('min' => 1, 'max' => 1));
        
        if ($uploadStatus['bandwidth']['remainingbytes'] > 0) {
            $maxFileSize = $uploadStatus['filesize']['maxbytes'];
        } else {
            $maxFileSize = 0;
        }
        
        $file->addValidator('Size', false, $maxFileSize);
        
        // hack for not showing 'the file exceeds the defined form size'
        $file->setMaxFileSize($maxFileSize*2);
        
        /*$file->setMultiFile(1);*/
        
        $this->addElement($file, 'file');
        
        $title = $this->addElement('text', 'title', array(
            'label'      => 'Title:',
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 100))
                )
        ));
        
        $short = $this->addElement('text', 'short', array(
            'label'      => 'Short description:',
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 120)),
                )
        ));
        
        $description = $this->addElement('textarea', 'description', array(
            'label'      => 'Description:',
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 4096)),
                )
        ));
    }
}
