<?php

class AgendaController extends Zend_Controller_Action
{
    public function init()
    {
        $registry = Zend_Registry::getInstance();
        
        $auth = Zend_Auth::getInstance();
        
        if (! $auth->hasIdentity()) {
            Zend_Controller_Front::getInstance()->registerPlugin(new ML_Plugins_LoginRedirect());
        }
    }
    
    public function indexAction()
    {
        $agenda = ML_Agenda::getInstance();
        $form = $agenda->form();
        
        $this->view->agendaForm = $form;
    }
}