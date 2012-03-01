#MediaLab
[![Build Status](https://secure.travis-ci.org/henvic/MediaLab.png?branch=master)](http://travis-ci.org/henvic/MediaLab)

## Presentation
This source code has it origins on the web service [Plifk](www.plifk.com) (the online version is pretty outdated however).
This code is no way perfetct. There are several bad design choices and lack of methodology in developing it. Work is in progress to make it better.

Influence: [Flickr](http://www.flickr.com/), [Twitter](http://twitter.com/), [Last.fm](http://last.fm), and [Multiply](http://multiply.com/) (when it was more like [Facebook](http://www.fcebook/.com/)) and more.

Two other web services were based on forks of this code base:

* [to-post.it](http://to-post.it/) is a minimalist Twitter-based blogging system
* [trazqueeupago.com](http://trazqueeupago.com/) is a flee market for the Twitter social network, with a GUI similar to that of to-post.it.

## Requirements
This was only tested on Mac OS X and Linux environments. It might also work on Windows.
Note that most of the system can work with a simpler setup, but don't assume this to be true.

### PHP and Servers
* [PHP](http://php.net/) >= 5.3.8
* [MySQL](http://www.mysql.com/) >= 5.1
* [memcached](http://memcached.org/) > 1.4
* [Apache CouchDB](http://couchdb.apache.org/) >= 1.0.1
* [Redis](http://redis.io/) >= 2.2.11
* [MongoDB](http://www.mongodb.org/) >= 2.0.2

Please note that the default memcached is insecure by design because it's freely accessible from everywhere. You must restrict access to it yourself.

### Extensions
* [XHP](http://github.com/facebook/xhp) (not in use)
* [uploadprogress](http://pecl.php.net/package/uploadprogress) (to be removed thanks to HTML 5)
* [memcached](http://php.net/memcached)
* [mongo](http://php.net/mongo)
* [ImageMagick](http://php.net/manual/en/book.imagick.php)
* [GeoIP](http://www.maxmind.com/app/php) (you need a MaxMind's database service for that, this library will be changed soon)

### PHP Libraries
* [Zend Framework](http://framework.zend.com/) >= 1.11.11
* [HTML Purifier](http://htmlpurifier.org/) >= 4.3.0 (get the standalone version)
* [phpass](http://www.openwall.com/phpass/) >= 0.3
* [PHP On Couch](https://github.com/dready92/PHP-on-Couch)
* [oauth-php](http://code.google.com/p/oauth-php/)
* [Predis](http://pearhub.org/projects/predis) >= 0.6.6
* [XHP php-lib](https://github.com/facebook/xhp/tree/master/php-lib) (not in use, don't mind)
* [twitter-async](https://github.com/jmathai/twitter-async)

For performance you want to strip the require_once's from the Zend framework code, see [How can I optimize my include_path?](http://framework.zend.com/manual/en/performance.classloading.html)

### Services
* [Amazon Web Services S3](http://aws.amazon.com/s3/) - profile pictures and files are stored with Amazon S3
* [Twitter API](https://dev.twitter.com/) - ([create your key](https://dev.twitter.com/apps))
* [GeoIP by MaxMind](http://www.maxmind.com/) - (get a [free] database, note we use a custom PHP extension rather than theirs)

## Install
You can install most of the extensions with [PECL](http://pecl.php.net/).

### Create the databases structures
MySQL tables have to be built. The DB scheme is at [application/configs/db.sql](https://github.com/henvic/MediaLab/blob/master/application/configs/db.sql)

The following CouchDB databases have to be created: *web_acess_log, and actions_log*.

### Points of entry
There are three modules: default, services and api. And also a simple redirector system.

#### The services module
It's command-line interface (CLI) based, not web based.
Point of entry: *bin/services*

#### Web based points of entry
* *public/index.php* for the default
* *public-api/index.php* for the API
* *public-redirector/index.php* for the redirector system

You have to set up your web server configurations with regard to these. Use virtual hosts.

#### Rewrite rules
If you use Apache as your web server, the following rewrite rules might be used so the system receives the requests sent to it:

```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} -s [OR]
RewriteCond %{REQUEST_FILENAME} -l [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^.*$ – [NC,L]
RewriteRule ^.*$ index.php [NC,L]
```

For performance and to avoid the trouble with dealing with .htaccess I recommend not to use it (and disable it), instead put this on a Apache configuration file. Some text that might help you make a wise choice: [Remove index.php From URLs](http://expressionengine.com/wiki/Remove_index.php_From_URLs)

Note /index.php is hard-coded to return a 404 Not Found to make sure you do the proper thing and to avoid duplicates.

### Environmental variables you have to set

You have to set the environmental variables listed in the example below.

```
EXTERNAL_LIBRARY_PATH=/path/to/the/external-libraries/directory
APPLICATION_ENV=development
DEFAULT_TIMEZONE=GMT
PLIFK_CONF_FILE=/path/to/your/application/configs/applicaion.ini)
APPLICATION_PATH=/path/to/the/project/<application-directory>
CACHE_PATH=/path/to/your/cache
```

They should be set separately to the very same values twice (this will be improved soon), one time for the web modules and the other for the CLI SAPI modules.

For the web: use *SetEnv* on your configuration file (.htaccess or a Apache's configuration file).

If you need help about setting them on your CLI check out:
[Making lasting changes](http://www.mcsr.olemiss.edu/unixhelp/environment/env3db.html)
[EnvironmentVariables](https://help.ubuntu.com/community/EnvironmentVariables)

## Scheduled tasks
Some operations might be expensive. For example: if a user removes his account it is not smart to start a batch delete operation of all his files right away (and let him waiting for it, for instance).
A way to solve this and other similar issues is to make use of a scheduled task to run from time to time and do this heavy work.


Right now there are only a few of crontab jobs as you can see on the example below:

```
20 * * * * /path/to/application/bin/service --action cleanfiles --controller garbage >> /dev/null 2>&1
20 3 */2 * * /path/to/application/bin/service --action cleanoldnewusers --controller garbage >> /dev/null 2>&1
30 4 */3 * * /path/to/application/bin/service --action cleanoldrecover --controller garbage >> /dev/null 2>&1
40 5 */4 * * /path/to/application/bin/service --action cleanoldemailchange --controller garbage >> /dev/null 2>&1
```

## Push, open bugs, etc.
Feel free to push code to this repository. Anything you want, go to the [issue tracker](https://github.com/henvic/MediaLab/issues/).

## License
This software is provided "as is", without warranty.
The [New BSD License](http://en.wikipedia.org/wiki/New_BSD_license) and the [MIT License](http://en.wikipedia.org/wiki/MIT_License) is the license (case you need something legal).

## Author
Henrique Vicente de Oliveira Pinto ([email](mailto:henriquevicente@gmail.com), [Twitter](https://twitter.com/henriquev), [Flickr](http://www.flickr.com/photos/henriquev), [Linkedin](http://linkedin.com/in/henvic)).