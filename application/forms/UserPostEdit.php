<?php
class Ml_Form_UserPostEdit extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_equipment;

    protected $_username;
    protected $_postId;
    protected $_type;

    public function __construct($options = null, Zend_Translate $translate, array $equipment, $username, $postId, $type)
    {
        $this->_equipment = $equipment;

        $this->_username = $username;
        $this->_postId = $postId;
        $this->_type = $type;

        $this->setTranslator($translate);

        return parent::__construct($options);
    }

    public function init()
    {
        $this->setMethod('post');
        $this->setAction($this->getView()->url(
            array("username" => $this->_username, "post_id" => $this->_postId), "user_post_edit")
        );

        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/',
            Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/',
            Zend_Form_Element::FILTER);
        $this->addPrefixPath("Ml_Form", "Ml/Form");

        $this->addElement('text', 'make', array(
            'label' => 'Marca',
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                'StringLength', 'options' => array(1, 30))
            )
        ));

        $this->addElement('text', 'model', array(
            'label' => 'Modelo',
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                'StringLength', 'options' => array(1, 30))
            )
        ));

        $this->addElement('text', 'name', array(
            'label'      => 'Nome (anúncio)',
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                'StringLength', 'options' => array(1, 30))
            ),
            'class' => 'input-xlarge'
        ));

        $this->addElement('text', 'price', array(
            'label'      => 'Preço',
            'prepend' => 'R$',
            'placeholder' => '00.000,00',
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                'StringLength', 'options' => array(1, 13)),
            ),
            'class' => 'input-small',
            'maxlength' => 13
        ));

        $this->getElement("price")->addFilter(new Ml_Filter_CurrencyBr());
        $this->getElement("price")->addValidator(new Ml_Validate_CurrencyBr());

        $thisYear = gmdate("Y");
        $years = ["" => "-"];
        for ($yearCount = $thisYear + 1; $yearCount >= $thisYear - 100; $yearCount--) {
            $years[$yearCount] = $yearCount;
        }

        $this->addElement('select', 'model_year', array(
            'label'      => 'Ano do modelo',
            'required'   => false,
            'multiOptions' => $years,
            'class' => 'input-small'
        ));

        $engineCcs = ["" => "-"];

        for ($engineCc = 0.8; $engineCc <= 6.8; $engineCc += 0.1) {
            $engineCcFormated = number_format($engineCc, 1);
            $engineCcs[$engineCcFormated] = $engineCcFormated;
        }

        $this->addElement('select', 'engine', array(
            'label' => 'Motor',
            'required' => false,
            'multiOptions' => ["Motor" => $engineCcs],
            'id' => 'post-product-engine',
            'class' => 'input-mini'
        ));

        $this->addElement('select', 'traction', array(
                'required' => false,
                'label'    => 'Tração',
                'multiOptions' => array(
                    "" => "-",
                    "front" => "Frontal",
                    "rear" => "Traseira",
                    "4x4" => "4x4"
                )
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
                )
            )
        );

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
                )
            )
        );

        $this->addElement('text', 'km', array(
            'label'      => 'Quilometragem',
            'required'   => false,
            'class' => 'input-mini no-spin-button',
            'pattern' => '[0-9]*',
            'maxlength' => 7,
            'append' => 'km'
        ));

        $this->getElement("km")->addFilter(new Zend_Filter_Digits());

        $this->getElement("km")->addValidator(new Zend_Validate_Int());

        $this->addElement(
            'checkbox',
            'armor',
            array(
                'label' => 'Blindado',
            )
        );

        $this->addElement(
            'checkbox',
            'handicapped',
            array(
                'label' => 'Adaptado para deficiente',
            )
        );

        $equipment = new Zend_Form_Element_MultiCheckbox('equipment');
        $equipment->setLabel('Acessórios principais');

        foreach ($this->_equipment as $equipmentGroupKey => $equipmentGroup) {
            foreach ($equipmentGroup as $eachEquipment) {
                $equipment->addMultiOption(
                    "equipment_" . $this->_type . "_" . $equipmentGroupKey . "_" . $eachEquipment,
                    "equipment_" . $this->_type . "_" . $equipmentGroupKey . "_" . $eachEquipment
                );
            }
        }

        $this->addElement($equipment);

        $this->addElement('text', 'youtube_video', array(
            'label' => 'YouTube',
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                'StringLength', 'options' => array(1, 20),
                ),
                array('validator' => 'Alnum')
            )
        ));

        $this->addElement('textarea', 'description', array(
            'label'      => 'Descrição',
            'description' =>
            '<small><button class="btn btn-mini btn-link html-formatting-popover">HTML formatting</button></small>',
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                'StringLength', 'options' => array(0, 4096)),
            ),
            'rows' => 5
        ));

        $this->getElement('description')->getDecorator('description')->setEscape(false);

        $this->addElement('select', 'status', array(
                'required' => false,
                'label'    => 'Status',
                'multiOptions' => array(
                    "" => "-",
                    "staging" => "staging",
                    "active" => "active",
                    "end" => "end"
                )
            )
        );

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
        $this->setAttrib('id', 'post-manager');
    }
}
