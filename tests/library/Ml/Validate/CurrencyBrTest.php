<?php

class Ml_Validate_CurrencyBrTest extends PHPUnit_Framework_TestCase
{
    public function priceProvider()
    {
        return array(
            array("", false),
            array("d,33", false),
            array("22,d", false),
            array("crap", false),
            array("11,22", true),
            array("4.400,22", true),
            array("0,40", true),
            array("440", false),
            array("44,66,77", false),
            array("15,533", false),
            array("16,1", false),
            array("59,00", true),
            array(array("sa"), false)
        );
    }

    /**
     * @param $price
     * @param $expectedIsValid
     * @dataProvider priceProvider
     */
    public function testCurrency($price, $expectedIsValid)
    {
        $currencyBr = new Ml_Validate_CurrencyBr();

        $isValid = $currencyBr->isValid($price);

        $this->assertSame($isValid, $expectedIsValid);
    }
}