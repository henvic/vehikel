<?php
/* Pictures model
 * 
 *  All the pictures uploaded by users
 *  are controlled by this model.
 *  
 *  /pictures/<picid>.jpg
 * 
 * to be saved as
 * ./public/content/pictures/<picid>.<size>.jpg
 * 
 * In the database (table pictures):
 * id -> picture id (picid)
 * 
 * user -> who uploaded the picture
 * size -> thumbnail, small, medium, large. As in Flickr.
 * 
 * specialtype ->
 * 	avatar
 * 
 */
class ML_Pictures extends Model_getModel
{
	protected $_name = "pictures";

	// resource is a list of available pictures objects users
	protected $resources = Array(
		"people",
	);
	
	
	/** Explantion for the $Sizes:
	 * [0] => urihelper: for the links, i.e., /pictures/<id>/s is for the small pic
	 * [1] => typeextension: for the picture uri, i.e., <id>_sq.jpg
	 * [2] => name: the name of the resource
	 * [3] => dimension: the largest possible dimension for that picture resource
	 * 
	 * # ucfirst() - Make a string's first character uppercase
	 */
	protected $Sizes = Array(
		Array("h", "_h", "huge", 2048),
		Array("b", "_b", "large", 1024),
		Array("m", "", "medium", 500),
		Array("s", "_m", "small", 240),
		Array("t", "_t", "thumbnail", 100),
		Array("sq", "_s", "square", 75),
	);
	
	// the types below are given as in the $Sizes array order
	protected $sizeTypes = Array("urihelper", "typeextension", "name", "dimension");
	
	/**
	 * @todo this function can be used in some other places where a listing is helpful. Abstract function.
	 * Usage
	 * @param array with key => value, where key is the type of information and value is what's looked or just what's looked
	 * @return array with a given's size datatable info and false in failure
	 */
	public function getSizeInfo($sizeNeedle)
	{
		$match = false;
		$hasKey = false;
		

		if(is_array($sizeNeedle))
		{
			$hasKey = true;
			
			$key = array_search(key($sizeNeedle), $this->sizeTypes, true);
			
			$sizeNeedle = current($sizeNeedle);
		}
		
		
		foreach($this->Sizes as $size)
		{
			if(in_array($sizeNeedle, $size))
			{
				if($hasKey)
				{
					if($size[$key] != $sizeNeedle) continue;
				}
				
				$match = array_combine($this->sizeTypes, $size);
			}
		}
		
		return $match;
	}
	
	/**
	 * Calls the function above for each size and return
	 * for every element $Sizes the appropriate data
	 * @return array with information for each size
	 */
	public function getSizesInfo()
	{
		$data = Array();
		foreach($this->Sizes as $size)
		{
			$data[] = $this->getSizeInfo(Array("name" => $size[2]));
		}
		
		return $data;
	}
	
	public function getPictures($resourcename, $resourceid)
	{
		return $this->fetchEntries(Array(
			"TypeOfObject" => $resourcename,
			"forObjectId" => $resourceid,
			));
	}
	
	public function newPicture($resourceName, $resourceId)
	{
		$userIdentity = Zend_Auth::getInstance()->getIdentity();
		
		// we see how many pictures there are, if there are too many
		// we won't alloc a space for the picture
		$data = $Pictures->getPictures($resourceName, $resourceId);
		
		if(sizeof($data) >= $registry->config->pictures->limit) return false;
		
		$insert = $this->insert(Array(
			"postBy" => $userIdentity,
			"TypeOfObject" => $resourcename,
			"forObjectId" => $resourceid,
		));
		
		$id = $this->getAdapter()->lastInsertId();
		
		return $id;
	}
	
	public function deletePicture($id)
	{
		$Pictures = new Model_Pictures();
		
		if(!is_natural_dbId($id)) return false;
		
		$Pictures->deleteByParam($id);
		
		foreach($this->getSizesInfo() as $sizeinfo)
		{
			unlink(APPLICATION_PATH ."/../public/content/pictures/".$id.$sizeinfo['typeextension'].'.jpg');
		}
		
		return true;
	}
	
	
	/*
	 * May want to use at sometime
	 * http://shiftingpixel.com/2008/03/03/smart-image-resizer/
	 */
	
	/*
	 * Get the picture size
	 */
	public function getPicture($id)
	{
		$registry = Zend_Registry::getInstance();
		$view = new Zend_View();
		$entry = $this->fetchEntry(Array("id" => $id));
		if(!$entry) return false;
		
		$files = Array();
		
		// the stored width/height params are for the huge;
		
		$max = ($entry['width'] > $entry['height']) ? 'width' : 'height';
		if($entry['width'] == 0 || $entry['height'] == 0) return false;
		
		$uri = "/pictures/$id/b/";
		
		// Get dimension and links for all the files
		foreach($this->Sizes as $key => $size)
		{
			// what are the dimensions?
			if($size[2] == 'square')
			{
				$width = $height = $size[3];
			} elseif($entry[$max] > $size[3])
			{
				 if($max == 'width')
				 {
				 	//calculate the aspect ratio and then apply
				 	$width = $size[3];
					$height = intval(($entry['height']/$entry['width'])*($size[3]));
				 } else {
					$height = $size[3];
					$width = intval(($entry['width']/$entry['height'])*($size[3]));
				 }
			} else {
				$width = $entry['width'];
				$height = $entry['height'];
			}

			$pixUri = $registry->config->pictures->httppath.$id.$size[1].'.jpg';
			$html = '<a href="'.$uri.'" title="'.$view->escape($entry['title']).'"><img src="'.$pixUri.'" width="'.$width.'" height="'.$height.'" alt="'.$view->escape($entry['title'])."\" /></a>\n";
			
			$files[$size[2]] = Array(
				"width" => $width,
				"height" => $height,
				"uri" => $pixUri,
				"html" => $html,
			);
		}
		
		$data = array_merge($entry, $files, Array("sizesinfo" => $this->getSizesInfo()));
		
		return $data;
	}
	
	/*public function getUserAvatarId($user_id)
	{
		$Avatar = $this->fetchEntry(
    			Array("user" => $user_id,
    			"specialtype" => "avatar",
    			));
		 
    	return (isset($Avatar['id'])) ? $Avatar['id'] : false;
	}*/
	/* change to a more general function name in getModel
	public function quantityOfPictures($data)
	{
		$entries = $this->fetchEntries($data);
		
		return (is_array($entries)) ? sizeof($entries) : false;
	}
	*/
	
	/*
	 * @param $file is the filename to resize
	 * @param @id is the id related to the final files
	 */
	public function processPicture($file, $id)
	{
		
		// we can't trust unfiltered data, so we don't store the original
		// we also don't need really huge images, so we limit how huge it can be...
		$Image = new phMagick($file);
		
		$Image->setImageQuality(90);
		
		$dim = $Image->getDimentions();
		
		if(!$dim) return false;
		list($width, $height) = $dim;
		
		// acho que o update abaixo está atualizando tudo...
		if(!$this->updateSecure(Array("width" => $width, "height" => $height), Array(Array("id" => $id)))) return false;
		
		$unsharp = "-unsharp 0x0.4";
		
		foreach($this->maxDimensions as $key => $maxDimension)
		{
			$Image->setSource($file);
			$Image->setDestination(APPLICATION_PATH ."/../public/content/pictures/".$id.$this->sizeLink[$key].'.jpg');
			if($key == "square")
			{
				$size = ($height < $width) ? $height : $width;
				
				// instead of the 0s I can make an option for the user to tell the offset
				// with javascript use...
				$Image->crop($size, $size, 0, 0, "center");
				$Image->resize($maxDimension, $maxDimension, $unsharp);
				break;
			}
			if($width < $maxDimension &&
				$height < $maxDimension && $key != 'huge')
				{
					copy($Image->getSource(), $Image->getDestination());
				} else {
				($width > $height) ? $Image->resize($maxDimension, 0, $unsharp) :
					$Image->resize(0, $maxDimension, $unsharp);
			}
		}
		
		return true;
	}
}