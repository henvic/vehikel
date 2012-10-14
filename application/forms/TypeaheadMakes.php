<?php

class Ml_Form_TypeaheadMakes extends Zend_Form
{
    public function init()
    {
        $this->setMethod('get');

        $this->addElement(
            'radio',
            'type',
            array(
                'required' => false,
                'label'    => 'Type',
                'filters' => array('StringToLower'),
                'multiOptions' => array(
                    "car" => "car",
                    "boat" => "boat",
                    "motorcycle" => "motorcycle"
                )
            )
        );
    }
}

