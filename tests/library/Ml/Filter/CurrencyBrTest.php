<?php

class Ml_Filter_CurrencyBrTest extends PHPUnit_Framework_TestCase
{
    public function priceProvider()
    {
        return array(
            array("", ""),
            array("d,33", "d,33"),
            array("22,d", "22,d"),
            array("crap", "crap"),
            array("11,22", "11,22"),
            array("44,22", "44,22"),
            array("7740", "7.740,00"),
            array("4403", "4.403,00"),
            array("44,66,77", "4.466,77"),
            array("15,533", "15,533"),
            array("16,1", "16,10"),
            array("59,", "59,00")
        );
    }

    /**
     * @param $price
     * @param $expectedFilteredPrice
     * @dataProvider priceProvider
     */
    public function testCurrency($price, $expectedFilteredPrice)
    {
        $currencyBr = new Ml_Filter_CurrencyBr();

        $filteredPrice = $currencyBr->filter($price);

        $this->assertSame($expectedFilteredPrice, $filteredPrice);
    }
}