<?php

/**
 * @author Henrique Vicente <henriquevicente@gmail.com>
 * @license public domain
 * @since 2009
 * @todo make the HTMLPurifier support utf-8 links and latin domains
 * @todo caching should be implemented
 */

require EXTERNAL_LIBRARY_PATH .
 "/htmlpurifier-standalone/HTMLPurifier.standalone.php";

class HTMLPurifier_AttrTransform_AValidator extends HTMLPurifier_AttrTransform
{
    var $name = 'Link validation';

    function transform($attr, $purifierConfig, $context) {
        //consider test: if external link...
        $attr['rel'] = 'nofollow';
        return $attr;
    }
}

class Ml_Model_HtmlPurifier
{
    protected $_purifier = null;

    public function __construct()
    {
        $purifierConfig = HTMLPurifier_Config::createDefault();
        $purifierConfig->set('Cache.DefinitionImpl', null);

        $purifierConfig->set('HTML.TidyLevel', 'medium');
        $purifierConfig->set('Core.EscapeInvalidChildren', true);
        $purifierConfig->set('Core.EscapeInvalidTags', true);
        $purifierConfig->set('AutoFormat.RemoveEmpty', true);
        $purifierConfig->set('AutoFormat.Linkify', true);
        $purifierConfig->set('Core.ColorKeywords', '');
        //|target was here at the a element and also somewhere else
        $purifierConfig->set('HTML.Allowed', 'p,a[href|title],strong,b,br,em,i,ins,u,del,s');
        $purifierConfig->set('AutoFormat.AutoParagraph', true);

        $def = $purifierConfig->getHTMLDefinition(true);
        //a rel nofollow http://htmlpurifier.org/phorum/read.php?3,1442,1661,quote=1

        $a = $def->addBlankElement('a');

        //here it is permited, in the function above it is applied w/the class
        $a->attr['rel'] = 'Enum#nofollow';

        $a->attr['title'] = 'Text';
        $a->attr['class'] = 'Text#new-window';//see above

        $a->attr_transform_post[] = new HTMLPurifier_AttrTransform_AValidator();

        $this->_purifier = new HTMLPurifier($purifierConfig);

        return $this->_purifier;
    }

    public function purify($html)
    {
        return $this->_purifier->purify($html);
    }
}
