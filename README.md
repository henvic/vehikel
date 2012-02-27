[![Build Status](https://secure.travis-ci.org/henvic/MediaLab.png?branch=master)](http://travis-ci.org/henvic/MediaLab)

Below there is a copy of the README file for now.

Please notice that the quality of this work vary a lot. This is my first project I release for the public. I'm sad it took me so long to do so. I hope something is useful.

This is incomplete, with parts that shouldn't even exists, with bad quality portions of code, etc. The code on production in www.plifk.com is very outdated related to this one, by the way.


README
***
# MediaLab
## Presentation
This is the code of a project I started by March 2009 but never had results in terms of users.

This project originated with the web service Plifk (www.plifk.com) which is a file sharing web service I created (the version currently running there is very outdated).

I started studying the Zend Framework and trying to do something. Not exactly in this order. Not knowing what I'd be creating next.

I was much influenced by Flickr, Twitter, Last.fm and Multiply when I start.

The web service is discontinued but I plan to use this with something else (yet thinking about what's next).

Before you ask me, I don't know if this name was given because of MIT's Media Lab or not. I don't recall.

I've launched two other sites that were built on a fork of this.

They're:
to-post.it: a minimalist Twitter-based blogging system
trazqueeupago.com: a fork of to-post.it, but with the focus on selling / buying instead and 'localized' to brazilian portuguese

I'm not working on any of these three services right now.

I think it might be useful for people looking to start using the amazing Zend Framework so I decided to make it available.

The following notes encompass everything - I hope - you need to know in order to run it as I run for plifk.com (or almost it).

## License
This is available 'as is'. If you have to consider a license I ask you consider either the MIT or the new BSD and it's fine.

## Quality problems?
There are some bad design decisions that were made earlier in the development of this project. One of them is abuse by using the Zend_Registry everywhere. Fix will come eventually (I intend that they're available really fast).

## Prerequisites
This system was never intended to be made possibly to be deployed easily anywhere so not regularly found extensions, databases and other mechanisms were used.

This list is given on a best effort basis and older versions may work or not.
* PHP >= 5.3.8
* MySQL >= 5.1.54
* memcached >= 1.4.5
* Apache CouchDB >= 1.0.1
* Redis >= 2.2.1 (not in use right now and don't have plans to start using it)

This system was tested with a virtual machine running Ubuntu and Zend Server 5.5 (with XHP and GeoIP extensions added).

### Not very common PHP extensions necessary:
* GeoIP http://www.maxmind.com/app/php
* uploadprogress http://pecl.php.net/package/uploadprogress
* XHP http://github.com/facebook/xhp
* memcached
* ImageMagick

The upload progress will be removed thanks to a new feature of HTML 5 that avoids its need.

XHP is not currently used at the time of the release of this document but will be soon after minor changes. More information regarding XHP can be found at https://www.facebook.com/notes/facebook-engineering/xhp-a-new-way-to-write-php/294003943919

For MaxMind's GeoIP to work it needs a database of its IP-geolocation information installed on the system.

### Libraries (and classes)
* Zend Framework >= 1.11.11 http://framework.zend.com/
* HTML Purifier (standalone version) >= 4.3.0 http://htmlpurifier.org/
* phpass >= 0.3 http://www.openwall.com/phpass/
* PHP On Couch >= unknown https://github.com/dready92/PHP-on-Couch
* oauth-php >= unknown http://code.google.com/p/oauth-php/
* Predis >= 0.6.6 http://pearhub.org/projects/predis (not in use right now)
* XHP php-lib >= unknown https://github.com/facebook/xhp/tree/master/php-lib
* twitter-async >= unknown https://github.com/jmathai/twitter-async
* PEAR
* UtfNormal >= unknown http://www.mediawiki.org/wiki/MediaWiki *

UtfNormal is a class of the MediaWiki package. Due to licensing restrictions I can't just copy it on this repository.
You've to download the MediaWiki package and copy the directory includes/normal to where the external libraries are located.

CouchDB is currently used for logging purposes now, but will be used for some other things later.

For performance you might want to strip all the require_once's from the Zend framework.
Please take a look at http://framework.zend.com/manual/en/performance.classloading.html

## Rewrite rules
In order for this system to work you need to always call the index.php file for the PHP requests made to it.

If you use Apache as your server you'll need some code rewrite rules like the following:

RewriteEngine On
RewriteCond %{REQUEST_FILENAME} -s [OR]
RewriteCond %{REQUEST_FILENAME} -l [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^.*$ – [NC,L]
RewriteRule ^.*$ index.php [NC,L]

If you can avoid using .htaccess and put this configuration on a Apache configuration file and disable .htaccess completely for performance's sake. For this same reason a .htaccess is not included.

Other rewrite rules might work better for you. Check out: http://expressionengine.com/wiki/Remove_index.php_From_URLs

If you are not using Apache please refer to literature to know how to do this step.

Important: any address starting with "http://<your-site's-home>/index.php" won't work (it will end up in a 404 Not Found). This is hardcoded and there to avoid having two pages with the same address which might be a SEO problem.

## Create databases structure
Some pre-configuration has to be made to the MySQL and CouchDB that will be used for the system.

At application/configs/db.sql there's the MySQL database that has to be created.

For CouchDB you just have to create the following databases: web_acces_log and actions_log

## Secure the system
You've to pay attention to secure your system properly. Please note that memcached doesn't provide security out of the box and might be freely accessible from everywhere if no steps are taken to secure it.

## TODO
Unit tests: tests/ is useless right now
Continue to implement HTML 5, right now the implementation is at an early stage and might be breaking standards / drafts because of that.
Start using Bootstrap with Less http://twitter.github.com/bootstrap/


## CRONTAB
Some expensive operations are scheduled instead of being done at the time of the requests.

Right now all of those are delete operations:
20 * * * * /home/web/plifk.com/distribution/production/services/program.php --action cleanfiles --controller garbage >> /dev/null 2>&1
20 3 */2 * * /home/web/plifk.com/distribution/production/services/program.php --action cleanoldnewusers --controller garbage >> /dev/null 2>&1
30 4 */3 * * /home/web/plifk.com/distribution/production/services/program.php --action cleanoldrecover --controller garbage >> /dev/null 2>&1
40 5 */4 * * /home/web/plifk.com/distribution/production/services/program.php --action cleanoldemailchange --controller garbage >> /dev/null 2>&1


The service module is designed to be run with PHP standalone.

You can run it with the CLI (command line interface) the following way:
./program.php --action controller.action





Other setup for this system:

AWS S3 account for some functionality (profile's picture & file upload system) is used.


The following environmental variables shall be set:

EXTERNAL_LIBRARY_PATH
APPLICATION_ENV (development/production)
DEFAULT_TIMEZONE (GMT)
PLIFK_CONF_FILE (where the configuration file resides, which may might be other than the application/configs/applicaion.ini)
APPLICATION_PATH
CACHE_PATH

They should be set on the Apache server with SetEnv and for the service module (CLI) it should be set as in
http://www.mcsr.olemiss.edu/unixhelp/environment/env3db.html
https://help.ubuntu.com/community/EnvironmentVariables

If you can't add them to public/index.php, public-redirector/index.php, public-, and system/....
For more information read the issue tracker at https://github.com/henvic/MediaLab/issues/


## Hacks to be fixed
At library/ML/RouteModule.php you can find Ml_Controller_Router_Route_Module.
It's almost a copy of Zend_Controller_Router_Route_Module, but there's some modifications to make it possible to use the tags system with internationalization like Flickr does. This is so that tags in languages such as japanese, for example, can be used on the URLs, like in http://link/<user>/tags/メインページ, for example. Stop using it and do the proper thing as soon as possible. It's only the way it is right now because it's legacy code from a long time ago.

## TODO
Start to use Facebook's Bootstrap (which uses LESS).
Create legacy/ directory to put old useless code that might be interesting for some reason.

## Author
I'm the irresponsible that released this code and I'm not sorry for that.
Henrique Vicente

http://linkedin.com/in/henvic

http://github.com/henvic

https://twitter.com/henriquev