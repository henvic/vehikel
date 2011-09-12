<?php

/**
 * Avatar
 * 
 * @author henrique
 *
 */
class Ml_View_Helper_avatar extends Zend_View_Helper_Abstract
{
     public function avatar($person, $size = "small")
     {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        $picture = Ml_Model_Picture::getInstance();
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        if (isset($person['people_deleted.id']) &&
         ! empty($person['people_deleted.id'])) {
            $uid = $person['people_deleted.id'];
            $name = $person['people_deleted.name'];
        } else if (isset($person['people.id'])) {
            $uid = $person['people.id'];
            $alias = $person['people.alias'];
            $name = $person['people.name'];
            $avatarInfo = $person['people.avatarInfo'];
        } else {
            $uid = $person['id'];
            $alias = $person['alias'];
            $name = $person['name'];
            $avatarInfo = $person['avatarInfo'];
        }
        
        if (isset($avatarInfo)) {
            $picInfo = unserialize($avatarInfo);
        }
        
        $sizeInfo = $picture->getSizeInfo($size);
        
        if (! isset($alias)) {
            //$html = '<img src="' .
            //$config['cdn'] .
            //'images/noavatar' .
            //$sizeInfo['typeextension'].'.gif" width="'.$sizeInfo['dimension'] .
            //'" height="'.$sizeInfo['dimension'].'" class="uid-' .
            //$uid.'" alt="" />';
            $html = '';
        } else if (! $picInfo || empty($picInfo)) {
            if ($sizeInfo['name'] == "square") {
                $height = $sizeInfo['dimension'];
            } else {
                $height = round($sizeInfo['dimension'] * 2 / 3);
            }
            
            $html = '<a href="' .
                 $router->assemble(array("username" => $alias), 
                "filestream_1stpage") . '/"><img src="' .
                 $config['cdn'] .
                 'images/happy-face' . $sizeInfo['typeextension'] .
                 '.png" width="' . $sizeInfo['dimension'] . '" height="' .
                 $height . '" alt="(' . $this->view->escape($alias) .
                 ' has no picture)"' . " class=\"uid-" . $uid . "\" /></a>\n";
        } else {
            $picUri = $config['services']['S3']['headshotsBucketAddress'] .
                $uid . '-' . $picInfo['secret'] . $sizeInfo['typeextension'] .
                '.jpg';
            
            if (isset($picInfo['sizes'][$sizeInfo['urihelper']]['w']) &&
             isset($picInfo['sizes'][$sizeInfo['urihelper']]['h'])) {
                $dim = ' width="' .
                 $picInfo['sizes'][$sizeInfo['urihelper']]['w'] .
                 '" height="' .
                 $picInfo['sizes'][$sizeInfo['urihelper']]['h'] . '"';
            } else {
                $dim = '';
            }
            
            $html = '<a href="' .
                 $this->view->url(array("username" => $alias), 
                "filestream_1stpage") . '" title="' .
                 $this->view->escape($name) .
                 '"><img src="' . $picUri . '"' . $dim . ' alt="' .
                 $this->view->escape($alias) . "\" class=\"uid-" . $uid .
                 "\" /></a>\n";
        }
        return $html;
     }
}
