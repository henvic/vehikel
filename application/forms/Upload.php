<?php
class Ml_Form_Upload extends Zend_Form
{
    public function init()
    {
        $registry = Zend_Registry::getInstance();
        
        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/', 
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
        
        $this->addElement(Ml_Model_MagicCookies::formElement());
        
        $this->addElement('submit', 'submitupload', array(
            'label'    => 'Upload!',
            'class'    => 'btn primary',
        ));
        
        $this->setAttrib('class', 'form-stacked');
    }
}
