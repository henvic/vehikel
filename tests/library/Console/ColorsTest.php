<?php

class Ml_Console_ColorsTest extends PHPUnit_Framework_TestCase
{
    public function testGetUncoloredString()
    {
        $colors = new Ml_Console_Colors();
        
        $string = "word";
        
        $result = $colors->getColoredString($string);
        
        $this->assertSame($string, $result);
    }
    
    public function testGetForegroundColors()
    {
        $colors = new Ml_Console_Colors();
        
        $foregroundColors = $colors->getForegroundColors();
        
        $this->assertContainsOnly("string", $foregroundColors);
        $this->assertContainsOnly("int", array_keys($foregroundColors));
        $this->assertNotEmpty($foregroundColors);
    }
    
    public function testGetBackgroundColors()
    {
        $colors = new Ml_Console_Colors();
        
        $backgroundColors = $colors->getBackgroundColors();
        
        $this->assertContainsOnly("string", $backgroundColors);
        $this->assertContainsOnly("int", array_keys($backgroundColors));
        $this->assertNotEmpty($backgroundColors);
    }

    /**
     * @depends testGetForegroundColors
     */
    public function testForegroundColoredString()
    {
        $colors = new Ml_Console_Colors();
        
        $string = "word";
        
        $expected = "\033[0;30m" . $string . "\033[0m";
        
        $availableForegroundColors = $colors->getForegroundColors();
        
        if (! in_array("black", $availableForegroundColors)) {
            $this->markTestSkipped("Color is not available.");
        }
        
        $actual = $colors->getColoredString($string, "black");
        
        $this->assertEquals($expected, $actual);
    }

    /**
     * @depends testGetBackgroundColors
     */
    public function testBackgroundColoredString()
    {
        $colors = new Ml_Console_Colors();
        
        $string = "word";
        
        $expected = "\033[40m" . $string . "\033[0m";
        
        $availableBackgroundColors = $colors->getBackgroundColors();
        
        if (! in_array("black", $availableBackgroundColors)) {
            $this->markTestSkipped("Color is not available.");
        }
        
        $actual = $colors->getColoredString($string, null, "black");
        
        $this->assertEquals($expected, $actual);
    }
    
    /**
     * @depends testGetForegroundColors
     * @depends testGetBackgroundColors
     */
    public function testAllColoredString()
    {
        $colors = new Ml_Console_Colors();
        
        $string = "word";
        
        $expected = "\033[0;30m\033[40m" . $string . "\033[0m";
        
        $availableForegroundColors = $colors->getForegroundColors();
        
        if (! in_array("black", $availableForegroundColors)) {
            $this->markTestSkipped("Color is not available.");
        }
        
        $availableBackgroundColors = $colors->getBackgroundColors();
        
        if (! in_array("black", $availableBackgroundColors)) {
            $this->markTestSkipped("Color is not available.");
        }
        
        $actual = $colors->getColoredString($string, "black", "black");
        
        $this->assertEquals($expected, $actual);
    }
}