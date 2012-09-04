<?php

class Ml_Model_RecoverTest extends PHPUnit_Framework_TestCase
{
    protected $_cache = null;

    public function uidProvider()
    {
        return array(
            array("300")
        );
    }

    public function outOfRangeProvider()
    {
        return array(
            array("f2", "39ba8b9xxx"),
            array("2223", "39ba8b9xxx")
        );
    }

    protected function setUp()
    {
        $this->_cache = new Zend_Cache_Core(array('automatic_serialization' => true));
        $backend = new Zend_Cache_Backend_File(array("cache_dir" => CACHE_PATH . "/tests"));
        $this->_cache->setBackend($backend);
    }

    /**
     * @param $uid
     * @dataProvider uidProvider
     */
    public function testCreateAndRetrieveAndDelete($uid)
    {
        $recover = new Ml_Model_Recover($this->_cache);

        $example = $recover->create($uid);

        $read = $recover->read($example["uid"], $example["security_code"]);

        $this->assertArrayHasKey("uid", $example);
        $this->assertArrayHasKey("security_code", $example);
        $this->assertEquals($example, $read, "Retrieved object is different than stored object");

        $delete = $recover->delete($example["uid"], $example["security_code"]);
        $this->assertTrue($delete, "Object could not be removed");
    }

    /**
     * @param $uid
     * @param $securityCode
     * @dataProvider outOfRangeProvider
     */
    public function testSecurityCodeOutOfRange($uid, $securityCode)
    {
        $recover = new Ml_Model_Recover($this->_cache);

        $read = $recover->read($uid, $securityCode);

        $delete = $recover->delete($uid, $securityCode);

        $this->assertFalse($read, "Out of range iud or security code should return false for reading");

        $this->assertFalse($delete, "Out of range uid or security code should return false for deleting");
    }

    /**
     * The error is simulated by using the BlackHole backend
     *
     * @param $uid
     * @dataProvider uidProvider
     */
    public function testStorageError($uid)
    {
        $backend = new Zend_Cache_Backend_BlackHole();
        $this->_cache->setBackend($backend);

        $recover = new Ml_Model_Recover($this->_cache);
        $getFalse = $recover->create($uid);

        $this->assertFalse($getFalse, "Returned not false when it should");
    }

    protected function tearDown()
    {
        $this->_cache->clean();
    }
}
