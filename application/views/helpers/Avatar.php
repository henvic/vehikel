<?php

/**
 * Avatar
 * 
 * @author henrique
 *
 */
class My_View_Helper_avatar extends Zend_View_Helper_Abstract
{
 	public function avatar($people_object, $size = "small")
 	{
 		$registry = Zend_Registry::getInstance();
 		$config = $registry->get("config");
		$Picture = ML_Picture::getInstance();
		
		if(isset($people_object['people_deleted.id']) && !empty($people_object['people_deleted.id']))
		{
			$uid = $people_object['people_deleted.id'];
			$name = $people_object['people_deleted.name'];
		}
		elseif(isset($people_object['people.id']))
		{
			$uid = $people_object['people.id'];
			$alias = $people_object['people.alias'];
			$name = $people_object['people.name'];
			$avatarInfo = $people_object['people.avatarInfo'];
		} else {
			$uid = $people_object['id'];
			$alias = $people_object['alias'];
			$name = $people_object['name'];
			$avatarInfo = $people_object['avatarInfo'];
		}
		
		if(isset($avatarInfo)) $picInfo = unserialize($avatarInfo);
		$sizeInfo = $Picture->getSizeInfo($size);
		
		if(!isset($alias))
		{
			//$html = '<img src="'.$config['services']['S3']['designBucketAddress'].'images/noavatar'.$sizeInfo['typeextension'].'.gif" width="'.$sizeInfo['dimension'].'" height="'.$sizeInfo['dimension'].'" class="uid-'.$uid.'" alt="" />';
			$html = '';
		}
		elseif(!$picInfo || empty($picInfo))
    	{
    		$height = ($sizeInfo['name'] == "square") ? $sizeInfo['dimension'] : round($sizeInfo['dimension']*2/3);
    		$html = '<a href="'.Zend_Controller_Front::getInstance()->getRouter()->assemble(array("username" => $alias), "filestream_1stpage").'/"><img src="'.$config['services']['S3']['designBucketAddress'].'images/happy-face'.$sizeInfo['typeextension'].'.png" width="'.$sizeInfo['dimension'].'" height="'.$height.'" alt="('.$this->view->escape($alias).' has no picture)"'." class=\"uid-".$uid."\" /></a>\n";
    	} else {
    		$picUri = $config['services']['S3']['headshotsBucketAddress'].$uid.'-'.$picInfo['secret'].$sizeInfo['typeextension'].'.jpg';
    		
    		$dim = (isset($picInfo['sizes'][$sizeInfo['urihelper']]['w']) && isset($picInfo['sizes'][$sizeInfo['urihelper']]['h'])) ? ' width="'.$picInfo['sizes'][$sizeInfo['urihelper']]['w'].'" height="'.$picInfo['sizes'][$sizeInfo['urihelper']]['h'].'"' : '';
    		
    		$html = '<a href="'.$this->view->url(array("username" => $alias), "filestream_1stpage").'" title="'.$this->view->escape($name).'"><img src="'.$picUri.'"'.$dim.' alt="'.$this->view->escape($alias)."\" class=\"uid-".$uid."\" /></a>\n";
    	}
		return $html;
 	}
}
