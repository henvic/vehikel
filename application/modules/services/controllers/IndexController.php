<?php
class IndexController extends Zend_Controller_Action
{
    public function indexAction() {
        echo "Welcome to the CLI SAPI.\nTry `--help` for more information.\n";
    }
}
