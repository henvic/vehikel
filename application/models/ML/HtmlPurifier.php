<?php

/**
 * Henrique Vicente
 * http://flickr.com/photos/henriquev/
 * 
 * henriquevicente at gmail dot com
 * 
 * This is of nobody; public domain;
 */

//@todo this is not right for utf-8 addresses: htmlpurifier currently doesn't support 'latin domains'
require EXTERNAL_LIBRARY_PATH."/htmlpurifier-standalone/HTMLPurifier.standalone.php";

class HTMLPurifier_AttrTransform_AValidator extends HTMLPurifier_AttrTransform
{
    var $name = 'Link validation';

    function transform($attr, $purifier_config, $context) {
        //consider test: if external link...
        $attr['rel'] = 'nofollow';
        $attr['class'] = 'new-window';
        return $attr;
    }
}

class ML_HtmlPurifier
{
    /**
     * Singleton instance
     */
    protected static $_instance = null;
    
    /**
     * Singleton pattern implementation makes "clone" unavailable
     *
     * @return void
     */
    protected function __clone()
    {}
    
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::init();
        }

        return self::$_instance;
    }
    
    protected static function init()
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        $purifier_config = HTMLPurifier_Config::createDefault();

        if(APPLICATION_ENV == "development")
        {
            $purifier_config->set('Cache.DefinitionImpl', null);
        } else {
            $purifier_config->set('Cache.SerializerPath', $config['htmlpurifier']['cachedir']);
        }

        //check http://htmlpurifier.org/docs/enduser-customize.html
        $purifier_config->set('HTML.DefinitionID', 'user-input data');

        //change these everytime the rules are updated to flush the cache
        $purifier_config->set('HTML.DefinitionRev', 523);
        $purifier_config->set('CSS.DefinitionRev', 52);

        $purifier_config->set('HTML.TidyLevel', 'medium');
        $purifier_config->set('Core.EscapeInvalidChildren', true);
        $purifier_config->set('Core.EscapeInvalidTags', true);
        $purifier_config->set('AutoFormat.RemoveEmpty', true);
        $purifier_config->set('AutoFormat.Linkify', true);
        $purifier_config->set('HTML.MaxImgLength', 1024);
        $purifier_config->set('Core.ColorKeywords', '');
        //|target was here at the a element and also somewhere else
        $purifier_config->set('HTML.Allowed', 'a[href|title],strong,b,br,em,i,img[src|alt|width|height|title],ins,del');

        $def = $purifier_config->getHTMLDefinition(true);
        //a rel nofollow http://htmlpurifier.org/phorum/read.php?3,1442,1661,quote=1



        $a = $def->addBlankElement('a');
        $a->attr['rel'] = 'Enum#nofollow';//here it is permited, in the function above it is applied w/the class
        $a->attr['title'] = 'Text';
        $a->attr['class'] = 'Text#new-window';//see above
        $a->attr_transform_post[] = new HTMLPurifier_AttrTransform_AValidator();

        $img = $def->addBlankElement('img');

        $img->attr['alt'] = 'Text';
        $img->attr['height'] = 'Pixels#'.$purifier_config->get("HTML.MaxImgLength");
        $img->attr['width'] = 'Pixels#'.$purifier_config->get("HTML.MaxImgLength");

        HTMLPurifier::instance($purifier_config);
        
        
        self::$_instance = new self();
    }

    public function purify($html)
    {
        $purifier = HTMLPurifier::instance();

        $purifying = $purifier->purify($html);

        //AutoFormat.AutoParagraph doesn't provide <br />
        $purified = nl2br($purifying);

        return $purified;
    }
}
