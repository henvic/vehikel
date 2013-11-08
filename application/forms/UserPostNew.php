<?php
class Ml_Form_UserPostNew extends Twitter_Bootstrap_Form_Horizontal
{
    public function __construct($options = null, Zend_Translate $translate)
    {
        $this->setTranslator($translate);

        return parent::__construct($options);
    }

    public function init()
    {
        $this->setMethod('post');

        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/',
            Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/',
            Zend_Form_Element::FILTER);
        $this->addPrefixPath("Ml_Form", "Ml/Form");

        $this->addElement('select', 'type', array(
            'label'      => 'Veículo',
            'required'   => true,
            'multiOptions' => ["car" => "Carro", "motorcycle" => "Motocicleta"],
            'class' => 'input-small',
            'id' => 'post-product-type-new'
        ));

        $this->addElement('text', 'make', array(
            'label' => 'Marca',
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                'StringLength', 'options' => array(1, 30))
            ),
            'id' => 'post-product-make-new'
        ));

        $this->addElement('text', 'model', array(
            'label' => 'Modelo',
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                'StringLength', 'options' => array(1, 30))
            ),
            'id' => 'post-product-model-new'
        ));

        $this->addElement('text', 'price', array(
            'label'      => 'Preço',
            'prepend' => 'R$',
            'placeholder' => '00.000,00',
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                'StringLength', 'options' => array(1, 13)),
            ),
            'class' => 'input-small',
            'maxlength' => 13,
            'id' => 'post-product-price-new'
        ));

        $this->getElement("price")->addFilter(new Ml_Filter_CurrencyBr());
        $this->getElement("price")->addValidator(new Ml_Validate_CurrencyBr());

        $thisYear = gmdate("Y");
        $years = ["" => "-"];
        for ($yearCount = (int) $thisYear + 1; $yearCount >= $thisYear - 100; $yearCount--) {
            $years[$yearCount] = $yearCount;
        }

        $this->addElement('select', 'model_year', array(
            'label'      => 'Ano do modelo',
            'required'   => false,
            'multiOptions' => $years,
            'class' => 'input-small'
        ));

        $this->addElement('text', 'km', array(
            'label'      => 'Quilometragem',
            'required'   => false,
            'class' => 'input-mini no-spin-button',
            'pattern' => '[0-9]*',
            'maxlength' => 7,
            'append' => 'km'
        ));

        $this->addElement('select', 'fuel', array(
                'required' => false,
                'label'    => 'Combustível',
                'multiOptions' => array(
                    "" => "-",
                    "flex" => "Flexível",
                    "gasoline" => "Gasolina",
                    "ethanol" => "Etanol",
                    "diesel" => "Diesel",
                    "other" => "Outro"
                ),
                'class' => 'input-small'
            )
        );

        $this->addElement(
            'select',
            'transmission',
            array(
                'required' => false,
                'label'    => 'Câmbio',
                'multiOptions' => array(
                    "" => "-",
                    "manual" => "Manual",
                    "automatic" => "Automático"
                ),
                'class' => 'input-small'
            )
        );

        $this->addElement('text', 'engine', array(
            'label' => 'Motor',
            'required' => false,
            'id' => 'post-product-engine',
            'class' => 'input-mini none'
        ));

        $this->addElement('submit', 'submit', array(
            'label'    => 'Salvar',
            'class' => 'btn btn-primary'
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
