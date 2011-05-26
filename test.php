<?php

/** 
* XMLToArray Generator Class 
* @author  :  MA Razzaque Rupom <rupom_315@yahoo.com>, <rupom.bd@gmail.com> 
*             Moderator, phpResource (LINK1http://groups.yahoo.com/group/phpresource/LINK1) 
*             URL: LINK2http://www.rupom.infoLINK2 
* @version :  1.0 
* @date       06/05/2006 
* Purpose  : Creating Hierarchical Array from XML Data 
* Released : Under GPL 
*/ 

class XmlToArray 
{ 
    
    var $xml=''; 
    
    /** 
    * Default Constructor 
    * @param $xml = xml data 
    * @return none 
    */ 
    
    function XmlToArray($xml) 
    { 
       $this->xml = $xml;    
    } 
    
    /** 
    * _struct_to_array($values, &$i) 
    * 
    * This is adds the contents of the return xml into the array for easier processing. 
    * Recursive, Static 
    * 
    * @access    private 
    * @param    array  $values this is the xml data in an array 
    * @param    int    $i  this is the current location in the array 
    * @return    Array 
    */ 
    
    function _struct_to_array($values, &$i) 
    { 
        $child = array(); 
        if (isset($values[$i]['value'])) array_push($child, $values[$i]['value']); 
        
        while ($i++ < count($values)) { 
            switch ($values[$i]['type']) { 
                case 'cdata': 
                array_push($child, $values[$i]['value']); 
                break; 
                
                case 'complete': 
                    $name = $values[$i]['tag']; 
                    if(!empty($name)){ 
                    $child[$name]= ($values[$i]['value'])?($values[$i]['value']):''; 
                    if(isset($values[$i]['attributes'])) {                    
                        $child[$name] = $values[$i]['attributes']; 
                    } 
                }    
              break; 
                
                case 'open': 
                    $name = $values[$i]['tag']; 
                    $size = isset($child[$name]) ? sizeof($child[$name]) : 0; 
                    $child[$name][$size] = $this->_struct_to_array($values, $i); 
                break; 
                
                case 'close': 
                return $child; 
                break; 
            } 
        } 
        return $child; 
    }//_struct_to_array 
    
    /** 
    * createArray($data) 
    * 
    * This is adds the contents of the return xml into the array for easier processing. 
    * 
    * @access    public 
    * @param    string    $data this is the string of the xml data 
    * @return    Array 
    */ 
    function createArray() 
    { 
        $xml    = $this->xml; 
        $values = array(); 
        $index  = array(); 
        $array  = array(); 
        $parser = xml_parser_create(); 
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1); 
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0); 
        xml_parse_into_struct($parser, $xml, $values, $index); 
        xml_parser_free($parser); 
        $i = 0; 
        $name = $values[$i]['tag']; 
        $array[$name] = isset($values[$i]['attributes']) ? $values[$i]['attributes'] : ''; 
        $array[$name] = $this->_struct_to_array($values, $i); 
        return $array; 
    }//createArray 
    
    
}//XmlToArray 





















$cache_refresh_time = 160;//seconds
//$feed_url = 'http://rss.slashdot.org/Slashdot/slashdot';
$feed_url = 'test.xml';
$limit = 4;

//checks if there is a cached file new enough to be used
//this is not smart (why a separate file for this?), but it still works just fine
//$ts_handle = fopen("tmp/cached_feed_timestamp", "r+");
//$timestamp = fread($ts_handle, 20);
//fclose($ts_handle);
//if(time() > $timestamp + $cache_refresh_time)
//{
//Creating Instance of the Class 
$xmlObj    = new XmlToArray(file_get_contents($feed_url)); 
//Creating Array 
$arrayData = $xmlObj->createArray(); 

print_r($arrayData);
//        if( $xml = simplexml_load_file($feed_url))
       // {
                //$string = "";
                //var_dump($xml);
                /*foreach($xml->channel->item as $news)
                {
                        $limit--;
                        $string.= '<li><p class="date">'.date("d/m/Y", strtotime($news->pubDate)).'</p><h3><a href="'.htmlspecialchars($news->link, ENT_COMPAT, "UTF-8", true).'">$
                        if(!$limit) {
                                break;
                        }
                }*/

 /*               $handle = fopen("tmp/cached_feed", "w+");
                fwrite($handle, $string, 8000);
                fclose($handle);
                $ts_handle = fopen("tmp/cached_feed_timestamp", "w+");
                fwrite($ts_handle, time(), 16);
                fclose($ts_handle);*/
       // }
//} else {
//        readfile("tmp/cached_feed");
//}

