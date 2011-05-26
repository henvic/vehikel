<?php
class Form_Upload extends Zend_Form
{	
    public function init()
    {
    	$registry = Zend_Registry::getInstance();
    	
    	$this->addElementPrefixPath('MLValidator', 'ML/Validators/', Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('MLFilter', 'ML/Filters/', Zend_Form_Element::FILTER);
    	
    	$signedUserInfo = $registry->get('signedUserInfo');
    	$uploadStatus = $registry->get("uploadStatus");
    	
    	$this->setAttrib('enctype', 'multipart/form-data');
        $this->setMethod('post');
        
        $File = new Zend_Form_Element_File('file');
        $File->setRequired(false);
		$File->setLabel('What do you want to share today?');
		
		$maxFileSize = ($uploadStatus['bandwidth']['remainingbytes'] > 0) ? $uploadStatus['filesize']['maxbytes']: 0;
		
		$File->addValidator('Size', false, $maxFileSize);
		
		$File->setMaxFileSize($maxFileSize);
		//$File->setMaxFileSize($maxFileSize*2);// hack for not showing 'the file exceeds the defined form size'
		
		$File->setMultiFile(4);
		
		$File->addValidator('Count', false, array('min' => 0, 'max' => 4));
		
		$this->addElement($File, 'file');
		
		$this->addElement(ML_MagicCookies::formElement());
		
        $this->addElement('submit', 'submitupload', array(
            'label'    => 'Upload!',
        ));
    }
}
