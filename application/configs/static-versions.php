<?php
/**
 * This is required by the My_View_Helper_staticversion
 */

$cacheFiles = array(
//    "/javascript/password.js" => "/scripts/password-min.v3.js",
//    "/javascript/progressbar.js" => "/scripts/progressbar-min.v4.js",
//    "/javascript/username-avail.js" => "/scripts/username-avail-min.v4.js",
    "/images/share-your-files.png" =>  "/images/share-your-files.v3.png",
);

/* sadly Amazon S3 doesn't use compression, let's try this for the very basic files */
$more_files = array(
        "/javascript/plifk.js" => "/scripts/plifk-min.v4.js",
        "/plifk.css" => "/style/plifk-min.v16.css",
    );
/*if(substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], "gzip"))
{
    $more_files = array(
        "/javascript/plifk.js" => "/scripts/plifk-min.v1.gz.js",
        "/plifk.css" => "/style/plifk-min.v14.gz.css",
    );
} else
{
    $more_files = array(
        "/javascript/plifk.js" => "/scripts/plifk-min.v1.js",
        "/plifk.css" => "/style/plifk-min.v14.css",
    );
}*/

$cacheFiles = array_merge($cacheFiles, $more_files);