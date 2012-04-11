<?php
class Ml_Form_Comment extends Zend_Form
{
    public function init()
    {    
        if (Zend_Registry::getInstance()->isRegistered("commentInfo")) {
            $altLabel = true;
        }
        
        $this->setMethod('post');
        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/', 
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
            'placeholder' => "Write your comment here",
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
            'required' => false,
            'class' => 'btn',
        ));
        
        $this->addElement('submit', 'commentPost', array(
            'label'    => $postCommentLabel,
            'required' => false,
            'class' => 'btn primary'
        ));
        
        $this->addElement(Ml_Model_MagicCookies::formElement());
    }
}
