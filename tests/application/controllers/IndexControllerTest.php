<?php
abstract class Controller_TestCase extends Zend_Test_PHPUnit_ControllerTestCase
{
    protected function setUp()
    {
        $this->bootstrap=array($this, 'appBootstrap');
        $storage = new Zend_Auth_Storage_NonPersistent();
        Zend_Auth::getInstance()->setStorage($storage);
        parent::setUp();
    }

    protected function tearDown()
    {
        Zend_Auth::getInstance()->clearIdentity();
    }

    protected function appBootstrap()
    {
        Application::setup();
    }
}
class IndexControllerTest extends ControllerTestCase
{
    public function testIndexAction() {
        $this->dispatch('/');
        $this->assertController('index');
        $this->assertAction('index');
    }

    public function testErrorURL() {
        $this->dispatch('foo');
        $this->assertController('error');
        $this->assertAction('error');
    }
}
//<?php
//require_once 'PHPUnit/Framework/TestCase.php';
///**
// * test case.
// */
//class IndexControllerTest extends PHPUnit_Framework_TestCase
//{
//    /**
//     * Prepares the environment before running a test.
//     */
//    protected function setUp ()
//    {
//        parent::setUp();
//         // TODO Auto-generated IndexControllerTest::setUp()
//    }
//    /**
//     * Cleans up the environment after running a test.
//     */
//    protected function tearDown ()
//    {
//        // TODO Auto-generated IndexControllerTest::tearDown()
//        parent::tearDown();
//    }
//    /**
//     * Constructs the test case.
//     */
//    public function __construct ()
//    {
//        // TODO Auto-generated constructor
//    }
//    
//    public function testCredential()
//    {
//        Ml_Credential::getInstance();
//        return true;
//    }
//}

