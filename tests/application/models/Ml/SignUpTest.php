<?php

class Ml_Model_SignUpTest extends PHPUnit_Framework_TestCase
{
    protected $_cache = null;

    public function userProvider()
    {
        return array(
            array("Foo", "foo@bar")
        );
    }

    public function securityCodeOutOfRangeProvider()
    {
        return array(
            array("39ba8b9xxx")
        );
    }

    protected function setUp()
    {
        $this->_cache = new Zend_Cache_Core(array('automatic_serialization' => true));
        $backend = new Zend_Cache_Backend_File(array("cache_dir" => CACHE_PATH . "/tests"));
        $this->_cache->setBackend($backend);
    }

    /**
     * @param $name
     * @param $email
     * @dataProvider userProvider
     */
    public function testCreateAndRetrieveAndDelete($name, $email)
    {
        $signUp = new Ml_Model_SignUp($this->_cache);

        $example = $signUp->create($name, $email);

        $read = $signUp->read($example["securitycode"]);

        $this->assertArrayHasKey("name", $example);
        $this->assertArrayHasKey("email", $example);
        $this->assertArrayHasKey("securitycode", $example);
        $this->assertEquals($example, $read, "Retrieved object is different than stored object");

        $delete = $signUp->delete($example["securitycode"]);
        $this->assertTrue($delete, "Object could not be removed");
    }

    /**
     * @param $securityCode
     * @dataProvider securityCodeOutOfRangeProvider
     */
    public function testSecurityCodeOutOfRange($securityCode)
    {
        $signUp = new Ml_Model_SignUp($this->_cache);

        $read = $signUp->read($securityCode);

        $delete = $signUp->delete($securityCode);

        $this->assertFalse($read, "Out of range security code should return false for reading");

        $this->assertFalse($delete, "Out of range security code should return false for deleting");
    }

    /**
     * The error is simulated by using the BlackHole backend
     *
     * @param $name
     * @param $email
     * @dataProvider userProvider
     */
    public function testStorageError($name, $email)
    {
        $backend = new Zend_Cache_Backend_BlackHole();
        $this->_cache->setBackend($backend);

        $signUp = new Ml_Model_SignUp($this->_cache);
        $getFalse = $signUp->create($name, $email);

        $this->assertFalse($getFalse, "Returned not false when it should");
    }

    protected function tearDown()
    {
        $this->_cache->clean();
    }
}
