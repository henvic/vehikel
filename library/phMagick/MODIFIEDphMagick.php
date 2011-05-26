<?php
/* MODIFIED BY ME, HENRIQUE VICENTE
 * TO ADD PARAM OT THE RESIZE FUNCTION $cmdline
 * to let the user send extra data to the cmd (i.e., -sharp x)
 * 
 * PLUS: it was stripped of this function: fromString(...)
 * (just bc PHP 4/5 compatibility issues w/ E_STRICT on)
 */
/*

	+--------------------------------------------------------------------------------------------+
	|                                                                                            |
	|                 HAVING PROBLEMS? NEED HELP? DOESN'T WORK? WANT TO SAY HELLO?               |
	|                                                                                            |
	|		                       WRITE ME, I'M GLAD TO HELP                                    |
	|                                                                                            |
	|                                SVEN@FRANCODACOSTA.COM                                      |
	|                                                                                            |
	+--------------------------------------------------------------------------------------------+
	
	
	+--------------------------------------------------------------------------------------------+
	|	PROJECT LINKS                                                                            |
	+--------------------------------------------------------------------------------------------+
	|                                                                                            |
	|    USAGE EXAMPLES:                                                                         |
	|          http://www.francodacosta.com/phmagick/usage-examples                              |
	|                                                                                            |
	|    PROJECT HOME:                                                                           |
	|          http://www.francodacosta.com/phmagick/                                            |
	|                                                                                            |
	|    ANNOUNCEMENTS FEED:                                                                     |
	|          http://www.francodacosta.com/category/announcements/feed                          |
	|                                                                                            |
	+--------------------------------------------------------------------------------------------+
	
	
	+--------------------------------------------------------------------------------------------+
	|	DISCLAIMER - LEGAL NOTICE - LICENCING (GPL V3)                                           |
	+--------------------------------------------------------------------------------------------+
	|                                                                                            |
	|  This program is free software; you can redistribute it and/or modify it under the terms   |
	|  of the GNU General Public License version 3 as published by the Free Software Foundation  |
	|                                                                                            |
	|  This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; |
	|  without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. |
	|  See the GNU General Public License for more details.                                      |
	|                                                                                            |
	|  You should have received a copy of the GNU General Public License along with this         |
	|  program, if not you can obtain it at http://www.gnu.org/licenses/gpl-3.0.html             |
	|                                                                                            |
	+--------------------------------------------------------------------------------------------+
	
	
	+--------------------------------------------------------------------------------------------+
	|	CHANGE LOG                                                                               |
	+--------------------------------------------------------------------------------------------+
	|                                                                                            |
	|   20081210 - added support for non transparent images like jpg to polaroid and             | 
	|              fakepolaroid (you can set the image background color)                         |
	|            - Added border color and shade color to polaroid                                |
	|            - set class vars to protrcted so phMagick can be sub-classed                    |
	|                                                                                            |
	|   20081124 - added ability to change system wide default text formats                      |
	|            - rotate() can now handle transparent backgrounds                               |
	|                                                                                            |
	|	20081122 - added -strip to resize (smaller file size)                                    |
	|			 - added default value for resize() $height                                      |
	|			 - added dropShadow()                                                            |
	|			 - added roundCorner()                                                           |
	|			 - added fakePolaroid()                                                          |
	|			 - added polaroid()                                                              |
	|	                                                                                         |
	|	20081121 - due to users requests invert() was renamed to inverColors(),                  |
	|              it makes more sence                                                           |
	|	                                                                                         |
	|	20081020 - added function acquire() to get x frames/pages from video or pdf              |
	|	                                                                                         |
	|			 - updated class url to the correct one                                          |
	|			                                                                                 |
	| 			 - bug:: added return this to setSource()                                        |
	| 			                                                                                 |
	| 			 - bug:: added return this to setWebserverPath()                                 |
	| 			                                                                                 |
	|	20081010 - ontheFly() :: removed dependency of CONFIG class                              |
	|			   (http://www.francodacosta.com/php/you-are-here-how-hard-can-it-be)            |
	|			   by adding setPhysicalPath() and setWebserverPath()                            |
	|			                                                                                 |
	|			 - bug:: fixed setImageQuality() not returning $this                             |
	|			                                                                                 |
	|			 - bug:: removed getHistory() extra return|                                      |
	|	                                                                                         |
	+--------------------------------------------------------------------------------------------+
		
*/


/**
 * phMagick - Image manipulation with Image Magick
 *
 * @version	0.3.2
 * @author		Nuno Costa - sven@francodacosta.com
 * @copyright	Copyright (c) 2007
 * @license	GPL v3 - http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://www.francodacosta.com/phmagick
 * @since		2008-03-13
 */
class phMagick{
    protected $imageMagickPath = '';
    protected $imageQuality = 80 ;
    
    protected $originalFile = '';
    protected $sourceFile = '';
    protected $destinationFile = '';
    protected $lastOutput = array();
    protected $history = array();
    
    protected $physicalPath = '';
    protected $webPath = '';
    
    public $debug = false ;
    
/*--------------	Private stuff	-----------------*/

    private function getBinary($binName){
        return $this->getImageMagickPath()  . $binName ;
    }
    
    private function execute($cmd){
        $ret = 100 ;
        $out = array();
    	exec($cmd,$out,$ret);
    	$this->setOutput( $out );
    	
    	if ($this->debug) echo "<p> $cmd (rc: $ret)</p>";
    	return $ret ;
    }
    
    public function setHistory($path){
    	$this->history[] = $path ;
    	return $this;
    }
    
    private function clearHistory(){
    	unset ($this->history);
    	$this->history = array();
    }
    
/*--------------    GETTERS & SETTERS    -----------------*/

    function setImageQuality($value){
        $this->imageQuality = intval($value);
        return $this;
    }
    
     function getImageQuality(){
        return $this->imageQuality;
    }
    
    //-----------------
     function setImageMagickPath ($path){
        
        if($path != '')
            if ( strpos($path, '/') < strlen($path))
                $path .= '/';
        
        $this->imageMagickPath = str_replace(' ','\ ',$path) ;
    }
    
     function getImageMagickPath (){
        return $this->imageMagickPath;
    }
    
    //-----------------
     function setSource ($path){
        $this->sourceFile = str_replace(' ','\ ',$path) ;
        return $this ;
    }
    
     function getSource (){
        return $this->sourceFile ;
    }
    
    //-----------------
     function setDestination ($path){
        $path = str_replace(' ','\ ',$path) ;
        $this->destinationFile = $path ;
        return $this;
    }
    
     function getDestination (){
    	if( ($this->destinationFile == '')){
    		$source = $this->getSource() ;
    		$ext = end (explode('.', $source)) ;
    		$this->destinationFile = dirname($source) . '/' . md5(microtime()) . '.' . $ext;
    	}
        return $this->destinationFile ;
    }
    
    //-----------------
    /**
     * Set the site root's physical bath ex: /home/francodacosta/phMagick
     * @param $path
     */
    function setPhysicalPath($path){
        $this->physicalPath = $path ;
        return $this ;
    }
    /**
     * Set the site root's url ex: http://francodacosta.com/phMagick
     * @param $path
     */
    function setWebserverPath($url){
        $this->webPath = $url ;
        return $this ;
    }
    
    //-----------------
    private function setOutput($out){
        $this->lastOutput = $out ;
    }
    
     function getOutput(){
        return $this->lastOutput;
    }

    
/*--------------    THE GOODIES    -----------------*/
    
    /**
	 *  @param String: The full path for the image
	 *  @param String: The full path for the newlly created image
	 */
     function __construct($sourceFile='', $destinationFile=''){
		$this->originalFile = $sourceFile;
		$this->sourceFile = $sourceFile ;
		$this->destinationFile = $destinationFile;
		
    }
    
    /**
     *	Resizes an image
     *
	 *  @param Mixed: The width of the image or '' to rezise porporcionallly to the height
	 *  @param Mixed: The height of the image or '' to rezise porporcionallly to the width
	 *  @param boolean: False: resizes the image to the exact porportions (aspect ration not preserved). True: preserves aspect ratio, only resises if image is bigger than specified measures
	 */
     function resize( $width, $height = 0, $cmdline = '', $exactDimentions = false){
    	
        $modifier = $exactDimentions ? '!' : '>';
        
        //if $width or $height == 0 then we want to resize to fit one measure
		//if any of them is sent as 0 resize will fail because we are trying to resize to 0 px
		$width  = $width  == 0 ? '' : $width ;
		$height = $height == 0 ? '' : $height ;
		
        $cmd = $this->getBinary('convert');
        $cmd .=  ' -scale "'. $width .'x'. $height . $modifier ;
        //$cmd .=  ' -resize "'. $width .'x'. $height . $modifier ;
        $cmd .= '" -quality '. $this->getImageQuality() ;
        $cmd .=  ' -strip ';
	$cmd .=  " $cmdline ";
        $cmd .= ' ' . $this->getSource() .' '. $this->getDestination();
    	
        
    	$this->execute($cmd);
    	$this->setSource($this->getDestination());
    	$this->setHistory($this->getDestination());
    	return  $this ;
    }
    
    
    /**
     * Creates a thumbnail of an image, if it doesn't exits
     *
     * Requires Config.php to convert path/url see http://www.francodacosta.com/php/you-are-here-how-hard-can-it-be
     *
     * @param String $imageUrl - The image Url
     * @param Mixed $width - String / Integer
     * @param Mixed $height - String / Integer
     * @param boolean: False: resizes the image to the exact porportions (aspect ration not preserved). True: preserves aspect ratio, only resises if image is bigger than specified measures
     *
     * @return String - the thumbnail URL
     */
    function onTheFly($imageUrl, $width, $height, $exactDimentions = false){

		
		//convert web path to physical
		$basePath = str_replace($this->webPath,$this->physicalPath, dirname($imageUrl) );
		$sourceFile = $basePath .'/'. basename($imageUrl); ;

		//naming the new thumbnail
		$thumbnailFile = $basePath . '/'.$width . '_' . $height . '_' . basename($imageUrl) ;
		
		$this->setSource($sourceFile);
		$this->setDestination($thumbnailFile);
		
		if (! file_exists($thumbnailFile)){
			$this->resize($width, $height, $exactDimentions);
		}
		
		if (! file_exists($thumbnailFile)){
		    //if there was an error, just use original file
			$thumbnailFile = $sourceFile;
		}
		
		//returning the thumbnail url
		return str_replace($this->physicalPath, $this->webPath, $thumbnailFile );
		
	}
	
	 function getDimentions(){
	    $cmd  = $this->getBinary('identify');
	    $cmd .= ' -format "%w,%h" ' . $this->getSource();
	    
	    $ret = $this->execute($cmd);
	    
    	if($ret == 0){
    		$out = $this->getOutput();
    		return explode(',', $out[0]);
    	}
	   return false ;
	    
	}
	
	/**
	 *
	 *	Darkens an image, defualt: 50%
	 *
	 * @param $imageFile String - Physical path of the umage file
	 * @param $newFile String - Physical path of the generated image
	 * @param $alphaValue Integer - 100: back , 0: original color (no change)
	 * @return boolean - True: success
	 */
	function darken($alphaValue = 50){
	    $percent = 100 - (int) $alphaValue;
	    
	    //get original file dimentions
	    
	    list ($width, $height) = $this->getDimentions();
	    
	    $cmd = $this->getBinary('composite');
        $cmd .=  ' -blend  ' . $percent . ' ';
        $cmd .= $this->getSource();
        $cmd .= ' -size '. $width .'x'. $height.' xc:black ';
        $cmd .= '-matte ' . $this->getDestination() ;
        
    	$this->execute($cmd);
    	$this->setSource($this->getDestination());
    	$this->setHistory($this->getDestination());
    	return  $this ;
	}
	
	/**
	 *
	 *	Brightens an image, defualt: 50%
	 *
	 * @param $imageFile String - Physical path of the umage file
	 * @param $newFile String - Physical path of the generated image
	 * @param $alphaValue Integer - 100: white , 0: original color (no change)
	 * @return boolean - True: success
	 */
	function brighten($alphaValue = 50){
	    
	    $percent = 100 - (int) $alphaValue;
	    
	    //get original file dimentions
	    
	    list ($width, $height) = $this->getDimentions();
	    
	    $cmd = $this->getBinary('composite');
        $cmd .=  ' -blend  ' . $percent . ' ';
        $cmd .= $this->getSource();
        $cmd .= ' -size '. $width .'x'. $height.' xc:white ';
        $cmd .= '-matte ' . $this->getDestination() ;
        
        $this->execute($cmd);
        $this->setSource($this->getDestination());
        $this->setHistory($this->getDestination());
    	return  $this ;
	}
	
	
	/**
	 *
	 * Joins severall imagens in one tab strip
	 *
	 * @param $paths Array of Strings - the paths of the images to join
	 */
	function tabStrip( Array $paths = null){
		if( is_null($paths) ) {
			$paths = $this->getHistory(phMagickHistory::returnArray);
		}
	    $cmd  = $this->getBinary('montage');
	    $cmd .= ' -geometry x+0+0 -tile x1 ';
	    $cmd .= implode(' ', $paths);
	    $cmd .= ' ' . $this->getDestination() ;
	    
	    $this->execute($cmd);
	    $this->setSource($this->getDestination());
	    $this->setHistory($this->getDestination());
    	return  $this ;
	}
	
	/**
	 * Add's an watermark to an image
	 *
	 * @param $watermarkImage String - Image path
	 * @param $gravity phMagickGravity - The placement of the watermark
	 * @param $transparency Integer - 1 to 100 the tranparency of the watermark (100 = opaque)
	 */
	function watermark($watermarkImage, $gravity, $transparency = 50){
		//composite -gravity SouthEast watermark.png original-image.png output-image.png
		$cmd   = $this->getBinary('composite');
		$cmd .= ' -dissolve ' . $transparency ;
		$cmd .= ' -gravity ' . $gravity ;
		$cmd .= ' ' . $watermarkImage ;
		$cmd .= ' ' . $this->getSource() ;
		$cmd .= ' ' . $this->getDestination() ;
		
		$this->execute($cmd);
		$this->setSource($this->getDestination());
		$this->setHistory($this->getDestination());
    	return  $this ;
	}
	
	/**
	 * Rotates an image X degrees
	 *
	 * @param $degrees Integer
	 */
	function rotate ($degrees){
		$cmd   = $this->getBinary('convert');
		$cmd .= ' -rotate ' . $degrees ;
		$cmd .= ' -background "none" ' . $this->getSource() ;
		$cmd .= ' ' . $this->getDestination() ;
		
		$this->execute($cmd);
		$this->setSource($this->getDestination());
		$this->setHistory($this->getDestination());
    	return  $this ;
	}
	
	/**
	 * Flips the image vericaly
	 * @return unknown_type
	 */
	function flipVertical(){
		$cmd  = $this->getBinary('convert');
		$cmd .= ' -flip ' ;
		$cmd .= ' ' . $this->getSource() ;
		$cmd .= ' ' . $this->getDestination() ;
		
		$this->execute($cmd);
		$this->setSource($this->getDestination());
		$this->setHistory($this->getDestination());
    	return  $this ;
	}
	
	/**
	 * Flips the image horizonaly
	 * @return unknown_type
	 */
	function flipHorizontal(){
		$cmd  = $this->getBinary('convert');
		$cmd .= ' -flop ' ;
		$cmd .= ' ' . $this->getSource() ;
		$cmd .= ' ' . $this->getDestination() ;
		
		$this->execute($cmd);
		$this->setSource($this->getDestination());
		$this->setHistory($this->getDestination());
    	return  $this ;
	}
	
	/**
	 *
	 * @param $width Integer
	 * @param $height Integer
	 * @param $top Integer - The Y coordinate for the left corner of the crop rectangule
	 * @param $left Integer - The X coordinate for the left corner of the crop rectangule
	 * @param $gravity phMagickGravity - The initial placement of the crop rectangule
	 * @return unknown_type
	 */
	function crop($width, $height, $top = 0, $left = 0, $gravity = 'center'){
		$cmd  = $this->getBinary('convert');
		$cmd .= ' ' . $this->getSource() ;

		if (($gravity != '')|| ($gravity != phMagickGravity::None) )  $cmd .= ' -gravity ' . $gravity ;

		$cmd .= ' -crop ' . (int)$width . 'x'.(int)$height ;
		$cmd .= '+' . $left.'+'.$top;
		$cmd .= ' ' . $this->getDestination() ;
		
		$this->execute($cmd);
		$this->setSource($this->getDestination());
		$this->setHistory($this->getDestination());
    	return  $this ;
	}
	
	/**
	 * Convert's the image to grayscale
	 */
	function toGrayScale(){
		$cmd  = $this->getBinary('convert');
		$cmd .= ' ' . $this->getSource() ;
		$cmd .= ' -colorspace Gray  ';
		$cmd .= ' ' . $this->getDestination() ;
		
		$this->execute($cmd);
		$this->setSource($this->getDestination());
		$this->setHistory($this->getDestination());
    	return  $this ;
	}
	
	/**
	 * Inverts the image colors
	 */
	function invertColors(){
		$cmd  = $this->getBinary('convert');
		$cmd .= ' ' . $this->getSource() ;
		$cmd .= ' -negate ';
		$cmd .= ' ' . $this->getDestination() ;
		
		$this->execute($cmd);
		$this->setSource($this->getDestination());
		$this->setHistory($this->getDestination());
    	return  $this ;
	}
	
	 function getHistory( $type = Null ){
		switch ($type){
			
			case phMagickHistory::returnCsv:
				return explode(',', array_unique($this->history));
				break;
				
			default:
			case phMagickHistory::returnArray :
				return array_unique($this->history) ;
				break;
				
		}
	}
	
	 function clear($clearTempFiles = 2){
		//clears history and destnation
		//removes any files used in history ;
		
		if($clearTempFiles > 0){
			if ($clearTempFiles == phMagickClear::keepLastFile) {
			// the last file in history will be kept
			// usually the last file is the result
				array_pop($this->history);
			}
			foreach($this->getHistory(phMagickHistory::returnArray) as $file){
				@unlink($file);
			}
		}
		
		$this->setDestination('');
		$this->clearHistory();
		
		return $this ;
	}
	
	 function copy($newFileName = ''){
		//emulates copy of destination file, so we can performs actions on a new file without changing the current one
		//sets orig = destination and set dest to $newFileName
		$this->setSource($this->getDestination());
		$this->setDestination($newFileName);
		
		return $this;
	}
	
	 function restart($newFileName = ''){
		//emulates copy of source file, so we can performs actions on a new file without changing the current one
		// sets orig to the orig file set at startup and set dest to $newFileName
		$this->setSource($this->originalFile);
		$this->setDestination($newFileName);
		
		return $this;
	}
	
	/**
	 * Attempts to create an image(s) from a File (PDF & Avi are supported on most systems)
	 * it grabs the first frame / page from the source file
	 * @param $file  String - the path to the file
	 * @param $ext   String - the extention of the generated image
	 */
	function acquireFrame($file){
	    $cmd = 'echo "" | '; //just a workarround for videos,
	    //                    imagemagick first converts all frames then deletes all but the first
	    $cmd .= $this->getBinary('convert');
		$cmd .= ' ' . $file .'[0]' ;
		$cmd .= ' ' . $this->getDestination() ;
		
		$this->execute($cmd);
		$this->setSource($this->getDestination());
		$this->setHistory($this->getDestination());
    	return  $this ;
	}
	
	function roundCorners($i = 15){
     
		
		//original idea from Leif ��strand <leif@sitelogic.fi>
        $cmd = $this->getBinary('convert');
        $cmd .= ' ' . $this->getSource() ;
        $cmd .= ' \( +clone  -threshold -1 ' ;
        $cmd .= "-draw 'fill black polygon 0,0 0,$i $i,0 fill white circle $i,$i $i,0' ";
        $cmd .= '\( +clone -flip \) -compose Multiply -composite ';
        $cmd .= '\( +clone -flop \) -compose Multiply -composite ';
        $cmd .= '\) +matte -compose CopyOpacity -composite ' ;
		$cmd .= ' ' . $this->getDestination() ;
		
		
		$this->execute($cmd);
		$this->setSource($this->getDestination());
		$this->setHistory($this->getDestination());
    	return  $this ;
	}
	
	function dropShaddow($color = '#000', $offset = 4, $transparency = 60){
          
        $cmd = $this->getBinary('convert');
        $cmd .= ' -page +4+4 ' . $this->getSource() ;
        $cmd .= ' -matte \( +clone -background "'. $color .'" -shadow '. $transparency.'x4+'.$offset.'+'.$offset.' \) +swap ';
        $cmd .= ' -background none -mosaic ';
		$cmd .= ' ' . $this->getDestination() ;
		
		$this->execute($cmd);
		$this->setSource($this->getDestination());
		$this->setHistory($this->getDestination());
    	return  $this ;
	}
	
	/**
	 * Fake polaroid effect (white border and rotation)
	 * 
	 * @param $rotation Int - The imahe will be rotatex x degrees
	 * @param $borderColor - Polaroid border (ussuay white)
	 * @param $shaddowColor - drop shaddow color
	 * @param $background - Image background color (use for jpegs or images that do not support transparency or you will end up with a black background)
	 */
	function fakePolaroid($rotate = 6 , $borderColor = "#fff", $background ="none"){
		$cmd = $this->getBinary('convert');
        $cmd .= ' ' . $this->getSource() ;
        $cmd .= ' -bordercolor "'. $borderColor.'"  -border 6 -bordercolor grey60 -border 1 -background  "none"   -rotate '. $rotate .' -background  black  \( +clone -shadow 60x4+4+4 \) +swap -background  "'. $background.'"   -flatten';
		$cmd .= ' ' . $this->getDestination() ;
		
		//echo $cmd .'<br>';;
		$ret = $this->execute($cmd);
		$this->setSource($this->getDestination());
		$this->setHistory($this->getDestination());
    	return  $this ;
	}
	
	/**
	 * Real polaroid efect, supports text
	 * 
	 * @param $format phMagickTextObject - text format for image label
	 * @param $rotation Int - The imahe will be rotatex x degrees
	 * @param $borderColor - Polaroid border (ussuay white)
	 * @param $shaddowColor - drop shaddow color
	 * @param $background - Image background color (use for jpegs or images that do not support transparency or you will end up with a black background)
	 */
	function polaroid( $format = null, $rotation= 6, $borderColor="snow", $shaddowColor = "black", $background="none"){
		
		
		
		if (get_class($format) == 'phMagickTextObject' ){
			//
		}else{
			$tmp = new phMagickTextObject();
			$tmp->text($format);
			$format = $tmp ;
		}
		
        $cmd = $this->getBinary('convert');
        $cmd .= ' ' . $this->getSource() ;
        
        
        if ($format->background !== false)
			$cmd .= ' -background "' . $format->background . '"';
			
		if ($format->color !== false)
			$cmd .= ' -fill "' . $format->color . '"' ;
			
		if ($format->font !== false)
			$cmd .= ' -font ' . $format->font ;
		
		if ($format->fontSize !== false)
			$cmd .= ' -pointsize ' . $format->fontSize ;
        
		if ($format->pGravity !== false)
			$cmd .= ' -gravity ' . $format->pGravity ;
			
		if ($format->pText != '')
			$cmd .= ' -set caption "' . $format->pText .'"';
        
        $cmd .= ' -bordercolor "'. $borderColor.'" -background "'.$background.'" -polaroid ' . $rotation .' -background "'. $background.'" -flatten ';
		$cmd .= ' ' . $this->getDestination() ;
		
		//echo $cmd .'<br>';;
		$this->execute($cmd);
		$this->setSource($this->getDestination());
		$this->setHistory($this->getDestination());
    	return  $this ;
	}
	
    
}

/*********************************************
*		Auxiliar classes / objects / values
**********************************************/

class phMagickTextObjectDefaults{
	public static $fontSize ='12';
	public static $font = false;
	
	public static $color = '#000';
	public static $background = false;
	
	public static $gravity = phMagickGravity::Center; //ignored in fromString()
	public $Text = '';
	
	private function __construct(){}
}


class phMagickTextObject {
	private $fontSize;
	private $font;
	
	private $color;
	private $background;
	
	private $pGravity; //ignored in fromString()
	private $pText = '';
	
	public function __construct(){
	    $this->fontSize   = phMagickTextObjectDefaults::$fontSize;
    	$this->font       = phMagickTextObjectDefaults::$font;
    	$this->color      = phMagickTextObjectDefaults::$color ;
    	$this->background = phMagickTextObjectDefaults::$background;
    	$this->pGravity   = phMagickTextObjectDefaults::$gravity;
	}
	
	function defaultFontSize($value){
	    phMagickTextObjectDefaults::$fontSize = $value;
	}
	
	function defaultFont($value){
	    phMagickTextObjectDefaults::$font = $value;
	}
	
	function defaultColor($value){
	    phMagickTextObjectDefaults::$color = $value;
	}
	
	function defaultBackground($value){
	    phMagickTextObjectDefaults::$background = $value;
	}
	
	function defaultGravity($value){
	    phMagickTextObjectDefaults::$gravity = $value;
	}
	
	
	
	function fontSize($i){
	    $this->fontSize = $i ;
	    return $this;
	}
	    
	function font($i){
	    $this->font = $i ;
	    return $this;
	}
	
	function color($i){
	    $this->color = $i ;
	    return $this;
	}
	
	function background($i){
	    $this->background = $i ;
	    return $this;
	}
	
	function __get($var){
	    return $this->$var ;
	}
	
	function gravity( $gravity){
		$this->pGravity = $gravity;
		return $this ;
	}
	
	function text( $text){
		$this->pText = $text;
		return $this ;
	}
}

class phMagickGravity{
	const None 		= 'None' ;
	const Center	= 'Center' ;
	const East		= 'East' ;
	const Forget	= 'Forget' ;
	const NorthEast	= 'NorthEast' ;
	const North		= 'North' ;
	const NorthWest	= 'NorthWest' ;
	const SouthEast	= 'SouthEast' ;
	const South		= 'South' ;
	const SouthWest	= 'SouthWest' ;
	const West		= 'West' ;
	
	private function __construct(){}
}

class phMagickHistory{
	const returnArray = 0 ;
	const returnCsv   = 1 ;
	
	private function __construct(){}
}

class phMagickClear {
	const keepAllFiles  = 0;
	const deletAllFiles = 1;
	const keepLastFile  = 2;
	
	private function __construct(){}
}


?>
