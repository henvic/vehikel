<?php

class Ml_Model_NumbersTest extends PHPUnit_Framework_TestCase
{
    // key is in base10, value is in base58
    // most data retrieved from http://flic.kr/p/<base58id>
    protected $_translations10to58base = array (
        "1" => "2",
        "5901327519" => "9ZtR5c",
        "5647874735" => "9B5QoB",
        "5510281090" => "9oVCAo",
        "5469616507" => "9kkdqT",
        "4630961153" => "84dTpP",
        "3562888023" => "6qQJtR",
        "2902586795" => "5quvVV",
        "1414287295" => "39YAkH",
        "281684113" => "qTGSD",
        "233002268" => "mAcsu",
        "19775544" => "2KmzE",
        "19410793" => "2Hu9R",
    );
    
    public function testEncodeBase58()
    {
        $numbers = new Ml_Model_Numbers();
        
        $results = array();
        
        foreach ($this->_translations10to58base as $base10 => $base58) {
            $results[$base10] = $numbers->base58Encode($base10);
        }
        
        $this->assertEquals($this->_translations10to58base, $results, "Failure in encoding to base58");
    }
    
    public function testDecodeBase58()
    {
        $numbers = new Ml_Model_Numbers();
        
        $results = array();
        
        foreach ($this->_translations10to58base as $base10 => $base58) {
            $testNum = $numbers->base58Decode($base58);
            $results[$testNum] = $base58;
        }
        
        $this->assertEquals($this->_translations10to58base, $results, "Failure in decoding from base58");
    }
}