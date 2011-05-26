<?php
class Form_Comment extends Zend_Form
{	
    public function init()
    {	
    	if(Zend_Registry::getInstance()->isRegistered("commentInfo")) {
    		$altLabel = true;
    	}
    	
        $this->setMethod('post');
        $this->addElementPrefixPath('MLValidator', 'ML/Validators/', Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('MLFilter', 'ML/Filters/', Zend_Form_Element::FILTER);
        
        $commentLabel = (isset($altLabel)) ? "Edit your comment:" : "Add your comment:";
        $postCommentLabel = (isset($altLabel)) ? 'Save Changes' : 'Post comment';
        $this->addElement('textarea', 'commentMsg', array(
            'label'      => $commentLabel,
            'required'   => true,
        	'description' => '<small><a href="/help/html" class="new-window">HTML formatting</a></small>',
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(1, 4096)),
                )
        ));
        
        $this->addElement('submit', 'getCommentPreview', array(
            'label'    => 'Preview',
        	'required' => false
        ));
        
        $this->addElement('submit', 'commentPost', array(
            'label'    => $postCommentLabel,
        	'required' => false
        ));
        
        $this->addElement(ML_MagicCookies::formElement());
    }
}
