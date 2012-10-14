<?php

class TypeaheadController extends Ml_Controller_Action
{
    public function indexAction()
    {
        $request = $this->getRequest();

        $search = $request->getParam("search");

        $word = $request->getParam("word", "");

        switch ($search) {
            case "makes" :
                $type = $request->getParam("type", "");

                $typeaheadMakes =  $this->_sc->get("typeaheadMakes");
                /** @var $typeaheadMakes \Ml_Model_TypeaheadMakes() */

                $form = new Ml_Form_TypeaheadMakes();

                if (! $form->isValid($request->getParams())) {
                    $items = [];
                } else {
                    $values = $form->getValues();
                    $items = $typeaheadMakes->getItems($values["type"]);
                }

                $word = '';
                break;
            case "models" :
                $typeaheadModels =  $this->_sc->get("typeaheadModels");
                /** @var $typeaheadModels \Ml_Model_TypeaheadModels() */

                $form = new Ml_Form_TypeaheadModels();

                if (! $form->isValid($request->getParams())) {
                    $items = [];
                } else {
                    $values = $form->getValues();
                    $allItems = $typeaheadModels->getByPart($values["type"], $values["make"], $values["word"]);

                    $items = [];
                    foreach ($allItems as $item) {
                        $items[] = $item["model"];
                    }
                }
            break;
            default: $items = [];
        }

        $data = [
            "word" => $word,
            "count" => sizeof($items),
            "values" => $items
        ];

        $this->_helper->json($data);

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
    }
}