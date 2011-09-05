<?php

class AgendaController extends Zend_Controller_Action
{
    public function init()
    {
        $registry = Zend_Registry::getInstance();
        if(!Zend_Auth::getInstance()->hasIdentity()) {
            Zend_Controller_Front::getInstance()->registerPlugin(new ML_Plugins_LoginRedirect());
        }
    }
    
    public function indexAction()
    {
        $Agenda = ML_Agenda::getInstance();
        $form = $Agenda->form();
        
        $this->view->agenda_form = $form;
    }
}