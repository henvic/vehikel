<?php
class Ml_Form_Comment extends Zend_Form
{
    public function init()
    {    
        if (Zend_Registry::getInstance()->isRegistered("commentInfo")) {
            $altLabel = true;
        }
        
        $this->setMethod('post');
        $this->addElementPrefixPath('Ml_Validator', 'Ml/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filters/', 
        Zend_Form_Element::FILTER);
        
        if (isset($altLabel)) {
            $commentLabel = "Edit your comment:";
            $postCommentLabel = "Save Changes";
        } else {
            $commentLabel = "Add your comment:";
            $postCommentLabel = "Post comment";
        }
        
        $this->addElement('textarea', 'commentMsg', array(
            'label'      => $commentLabel,
            'required'   => true,
            'description' =>
                '<small><a href="/help/html" class="new-window">' .
                'HTML formatting</a></small>',
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 4096)),
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
        
        $this->addElement(Ml_MagicCookies::formElement());
    }
}
