<?php

require_once EXTERNAL_LIBRARY_PATH . '/normal/UtfNormal.php';

class ML_TagsChange extends ML_Tags
{
    /**
     * RawFilter takes a string and process it
     *
     * @param $tag string
     * @return filtered raw tag
     */
    public function RawFilter($tagstring)
    {
        $tagstring = mb_ereg_replace(' +', ' ', trim($tagstring));
        $tagstring = mb_ereg_replace("[\r\t\n]", "", $tagstring);

        // http://www.asciitable.com/
        $tagstring = trim($tagstring, "\x22\x27\x26\x2C");

        if (ctype_punct($tagstring)) {
            $tagstring = '';
        }
        
        return $tagstring;
    }

    /**
     * Make the array of tags ready for storage
     *
     * @param $tags string
     * @return array of arrays of cleaned/raw tags
     */
    public function makeArrayOfTags($tags)
    {
        $tagsArray = array();
        $expTags = explode(" ", $tags);
        $tempArray = array();
        $openTag = 0;
        $counter = 0;

        if (is_string($tags)) {
            $tags = array($tags);
        }

        // making "words together"
        foreach ($expTags as $string) {
            if ($openTag == 0) {
                if (mb_substr($string, 0, 1) == '"') {
                    $openTag = 1;
                    $string = mb_substr($string, 1);
                }
                $tempArray[$counter] = $string;
                $counter++;
            } else {
                if (mb_substr($string, -1) == '"') {
                    $openTag = 0;
                    $string = mb_substr($string, 0, -1);
                }
                $tempArray[$counter-1] = $tempArray[$counter-1].' '.$string;
            }
        }

        //now we get see if there's anything empty
        //that maybe as a result of the openTags (and maybe something else),
        //so we only use the first found
        //or two things with the same cleantag
        $cleantags = array();
        foreach ($tempArray as $key => $string) {
            $rawString = $this->RawFilter($string);
            $cleanString = $this->normalize($rawString);
            
            if (!empty($rawString) && !empty($cleanString) &&
            !in_array($cleanString, $cleantags)) {
                if ($cleanString > 60) {
                    $cleanString = mb_substr($cleanString, 0, 59);
                }
                if ($rawString > 60) {
                    $rawString = mb_substr($rawString, 0, 59);
                }
                $tag = Array("raw" => $rawString, "clean" => $cleanString);
                $tagsArray[] = $tag;
                $cleantags[] = $cleanString;
            }
        }

        return $tagsArray;
    }

    /**
     * Normalize tags for search, etc (clean tag).
     *
     * @param $rawtag is a tag already filtered by the passToRawArray filter
     * @return normalized tag
     */
    public function normalize($rawtag)
    {
        // using http://svn.wikimedia.org/viewvc/mediawiki/trunk/phase3/includes/normal/

        /*Like Flickr:
         * http://weblog.terrellrussell.com/2007/06/clean-and-store-your-raw-tags-like-flickr/
         * $cleantag = mb_substr(mb_strtolower(preg_replace("/[\\s\"!@#\\$\\%^&*():\\-_+=\\'\\/.;`<>\\[\\]?\\\\]/", '', $rawtag)), 0, 60);
         * * doesn't work with non-ascii
         */
        $cleaning = $rawtag;
        
        $cleaning = UtfNormal::cleanUp($cleaning);
        $cleaning = mb_strtolower($cleaning, "UTF-8");

        $bye = Array(
        ' ', '\"', '\'', '!', '@', '$', '%', '&', '*', '(', ')',
        ':', '-', '_', '+', '=', '\'', '/', '.', ';', '`', '<', '>', '[', ']',
        '?', '\\', ',', '#', 
        );

        $cleaning = str_replace($bye, '', $cleaning);

        $cleantag = mb_substr($cleaning, 0, 50);

        if (empty($cleantag)) {
            return false;
        }

        return $cleantag;
    }
    
    /**
     *
     * @param $tagsString the tag string received by the means of a form
     * @param $shareInfo
     * @param $userInfo
     * @return unknown_type
     */
    public function add($tagsString, $shareInfo)
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        if (! $tagsString) {
            return false;
        }

        $shareId = $shareInfo['id'];
        $uid = $shareInfo['byUid'];
        //check the limit and avoids trying to put tags that are already there
        $oldTags = $this->get($shareId);
        $tagsCounter = sizeof($oldTags);
        $oldTagsCounter = $tagsCounter;
        $tagsLimit = $config['tags']['limit'];
        $tagsArray = $this->makeArrayOfTags($tagsString);

        $cleanOldTags = array();
        
        foreach ($oldTags as $tag) {
            $cleanOldTags[] = $tag['clean'];
        }

        foreach ($tagsArray as $n => $tag) {
            if ($tagsLimit <= $tagsCounter) {
                break;
            }
                
            if (! in_array($tag['clean'], $cleanOldTags)) {
                if ($tag['clean'] > 40) {
                    $tag['clean'] = mb_substr($tag['clean'], 0, 39);
                }
                if ($tag['raw'] > 40) {
                    $tag['raw'] = mb_substr($tag['raw'], 0, 39);
                }
                
                $tag['share'] = $shareId;
                $tag['people'] = $uid;
                
                try {
                    $this->insert($tag);
                    $tagsCounter++;
                } catch(Exception $e) {
                }
            }
        }
        
        return $tagsCounter - $oldTagsCounter;
    }
}