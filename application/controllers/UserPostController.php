<?php

class UserPostController extends Ml_Controller_Action
{
    use Ml_Controller_People;

    public function indexAction()
    {
        $people =  $this->_registry->get("sc")->get("people");
        /** @var $people \Ml_Model_People() */

        $form = new Ml_Form_ContactCarDealer();

        $this->view->contactCarDealerForm = $form;
    }
}