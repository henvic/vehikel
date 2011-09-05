<?php

/**
 * Avatar
 * 
 * @author henrique
 *
 */
class My_View_Helper_avatarprofile extends Zend_View_Helper_Abstract
{
     public function avatarprofile($people_object)
     {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        $Picture = ML_Picture::getInstance();
        
        $uid = $people_object['id'];
        $alias = $people_object['alias'];
        $name = $people_object['name'];
        $avatarInfo = $people_object['avatarInfo'];
        
        if(isset($avatarInfo)) $picInfo = unserialize($avatarInfo);
        $sizeInfo = $Picture->getSizeInfo("small");
        
        if(!$picInfo || empty($picInfo))
        {
            return false;
        } else {
            $picUri = $config['services']['S3']['headshotsBucketAddress'].$uid.'-'.$picInfo['secret'].$sizeInfo['typeextension'].'.jpg';
            
            $dim = (isset($picInfo['sizes'][$sizeInfo['urihelper']]['w']) && isset($picInfo['sizes'][$sizeInfo['urihelper']]['h'])) ? ' width="'.$picInfo['sizes'][$sizeInfo['urihelper']]['w'].'" height="'.$picInfo['sizes'][$sizeInfo['urihelper']]['h'].'"' : '';
            
            $html = '<a href="' . $config['services']['S3']['headshotsBucketAddress'].$uid.'-'.$picInfo['secret'].'-b.jpg' . '" title="'.$this->view->escape($name).'\'s picture" class="new-window"><img src="'.$picUri.'"'.$dim.' alt="'.$this->view->escape($alias)."\" class=\"uid-".$uid."\" /></a>\n";
        }
        return $html;
     }
}
