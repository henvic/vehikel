<?php
class Form_Upload extends Zend_Form
{
    public function init()
    {
        $registry = Zend_Registry::getInstance();
        
        $this->addElementPrefixPath('MLValidator', 'ML/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('MLFilter', 'ML/Filters/', 
        Zend_Form_Element::FILTER);
        
        $signedUserInfo = $registry->get('signedUserInfo');
        $uploadStatus = $registry->get("uploadStatus");
        
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setMethod('post');
        
        $file = new Zend_Form_Element_File('file');
        $file->setRequired(false);
        $file->setLabel('What do you want to share today?');
        
        if ($uploadStatus['bandwidth']['remainingbytes'] > 0) {
            $maxFileSize = $uploadStatus['filesize']['maxbytes'];
        } else {
            $maxFileSize = 0;
        }
        
        $file->addValidator('Size', false, $maxFileSize);
        
        $file->setMaxFileSize($maxFileSize);
        
        $file->setMultiFile(4);
        
        $file->addValidator('Count', false, array('min' => 0, 'max' => 4));
        
        $this->addElement($file, 'file');
        
        $this->addElement(ML_MagicCookies::formElement());
        
        $this->addElement('submit', 'submitupload', array(
            'label'    => 'Upload!',
        ));
    }
}
