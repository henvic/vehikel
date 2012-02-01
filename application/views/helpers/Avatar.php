<?php

/**
 * Avatar
 * 
 * @author henrique
 * @todo refactory this class
 *
 */
class Ml_View_Helper_Avatar extends Zend_View_Helper_Abstract
{
     public function avatar($person, $size = "small", $link = true, $dimension = array("width" => false, "height" => false))
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
            
            if ($dimension['height']) {
                $height = $dimension['height'];
            }
            
            if ($dimension['width']) {
                $width = $dimension['width'];
            } else {
                $width = $sizeInfo['dimension'];
            }
            
            $html = "";
            if ($link) {
                $html .= '<a href="' .
                 $router->assemble(array("username" => $alias), 
                "filestream_1stpage") . '/">';
            }
            $html .= '<img src="' .
                 $config['cdn'] .
                 'images/happy-face' . $sizeInfo['typeextension'] .
                 '.png" width="' . $this->view->escape($width) . '" height="' .
                 $this->view->escape($height) . '" alt="(' . $this->view->escape($alias) .
                 ' has no picture)"' . " class=\"uid-" . $uid . "\" />";
                 if ($link) {
                     $html .= "</a>\n";
                 }
        } else {
            $picUri = $config['services']['S3']['headshotsBucketAddress'] .
                $uid . '-' . $picInfo['secret'] . $sizeInfo['typeextension'] .
                '.jpg';
            
            if (isset($picInfo['sizes'][$sizeInfo['urihelper']]['w']) &&
             isset($picInfo['sizes'][$sizeInfo['urihelper']]['h'])) {
                if (isset($dimension['width'])) {
                    $width = $dimension['width'];
                } else {
                    $width = $picInfo['sizes'][$sizeInfo['urihelper']]['w'];
                }
                
                if (isset($dimension['height'])) {
                    $height = $dimension['height'];
                } else {
                    $height = $picInfo['sizes'][$sizeInfo['urihelper']]['h'];
                }
                
                
                $dim = ' width="' . $width .
                 '" height="' . $height . '"';
            } else {
                $dim = '';
            }
            
            $html = "";
            
            if ($link) {
                $html .= '<a href="' .
                 $this->view->url(array("username" => $alias), 
                "filestream_1stpage") . '" title="' .
                 $this->view->escape($name) .
                 '">';
            }
            $html .= '<img src="' . $picUri . '"' . $dim . ' alt="' .
                 $this->view->escape($alias) . "\" class=\"uid-" . $uid .
                 "\" />";
            
            if ($link) {
                $html .= "</a>\n";
            }
        }
        return $html;
     }
}
