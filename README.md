#vehikel
[![Build Status](https://secure.travis-ci.org/henvic/vehikel.png?branch=master)](http://travis-ci.org/henvic/vehikel)

## Presentation
This is built based on the [MediaLab](https://github.com/henvic/MediaLab) project.

## Requirements
Unless otherwise referenced to, the versions for the requirements are given by the install script or other way.

### PHP and Servers
* Unix-like system
* [PHP](http://php.net/) >= 5.4.x
* [MySQL](http://www.mysql.com/) >= 5.1
* [memcached](http://memcached.org/)
* [Apache CouchDB](http://couchdb.apache.org/)
* [Redis](http://redis.io/)
* [MongoDB](http://www.mongodb.org/)

Please note that the default memcached is insecure by design because it's freely accessible from everywhere. You must restrict access to it yourself.

### Extensions
* [memcached](http://php.net/memcached)
* [mongo](http://php.net/mongo)
* [GeoIP](http://www.maxmind.com/app/php) (you need a MaxMind's database service for that, this library will be changed soon)

### PHP Libraries
* [Zend Framework](http://framework.zend.com/)
* [HTML Purifier](http://htmlpurifier.org/) (use the standalone version)
* [phpass](http://www.openwall.com/phpass/)
* [PHP On Couch](https://github.com/dready92/PHP-on-Couch)
* [oauth-php](http://code.google.com/p/oauth-php/)
* [Predis](http://pearhub.org/projects/predis)
* [twitter-async](https://github.com/jmathai/twitter-async)

For performance you want to strip the require_once's from the Zend framework code, see [How can I optimize my include_path?](http://framework.zend.com/manual/en/performance.classloading.html)

### Client-side dependencies
[Bootstrap, from Twitter](http://twitter.github.com/bootstrap/)
[jQuery](http://jquery.com/)
[jQuery.fn.autoResize](https://github.com/padolsey/jQuery.fn.autoResize)
[Tablesorter](http://tablesorter.com/)
[RequireJS](http://requirejs.org)
[Backbone.js](http://backbonejs.org)


### Services
* [Amazon Web Services S3](http://aws.amazon.com/s3/) - pictures and static documents are stored with Amazon S3
* [Twitter API](https://dev.twitter.com/) - ([create your key](https://dev.twitter.com/apps))
* [GeoIP by MaxMind](http://www.maxmind.com/) - (get a [free] database, note we use a custom PHP extension rather than theirs)
* [reCAPTCHA](http://www.google.com/recaptcha) - captcha service

## Install

On your CLI (command-line interface):

```
npm install
cd bin
./install-vendors.sh
./install
```

*For development you will want to respond with "development" on the question about the application environment*


This will take care of setting application paths, downloading and setting up the dependencies.
It will write a *application/configs/Environment.php.dist similar to *[application/configs/Environment.php](https://github.com/henvic/vehikel/blob/master/application/configs/Environment.php)*, with the choosen timezone and directory and file paths.

If a extension is missing you will know it.
You can get most of them with [PECL](http://pecl.php.net/) or apt-get on a Debian-based system such as Ubuntu.

For example, if mongo is missing you can use:

```
sudo pecl channel-update pecl.php.net
sudo pecl install mongo
echo extension=mongo.so >> /etc/php.ini
```

### Create the databases structures
MySQL tables have to be built. The DB scheme is at *[application/configs/db.sql](https://github.com/henvic/vehikel/blob/master/application/configs/db.sql)*

The following CouchDB databases have to be created: *web_access_log, and actions_log*.

### Points of entry
There are three modules: default, services and api. And also a simple redirector system.

#### The services module
There is a CLI based services module.
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

For performance and to avoid the trouble of dealing with .htaccess I recommend not to use it (and disable it), instead put this on a Apache configuration file. Some text that might help you make a wise choice: [Remove index.php From URLs](http://expressionengine.com/wiki/Remove_index.php_From_URLs)

Note /index.php is hard-coded to return a 404 Not Found to make sure you do the proper thing and to avoid duplicates.

#### Static assets
You have static assets (on the *statics* directory) such as images, JavaScripts and CSS which your users need to access.

For production you should use a CDN service (such as S3 + CloudFront) to serve them.

If you install as a development environment a link is made inside *public*. It is *public/dev-static-link*.
It is useful especially for crafting the templates files with XHR restrictions issues (in production the files' contents are placed in .js files to overcome this).

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
Feel free to push code to this repository. Anything you want, go to the [issue tracker](https://github.com/henvic/vehikel/issues/).

## License
This software is provided "as is", without warranty.
The [New BSD License](http://en.wikipedia.org/wiki/New_BSD_license) and the [MIT License](http://en.wikipedia.org/wiki/MIT_License) are the licenses (case you need something legal).

## Author
Henrique Vicente de Oliveira Pinto ([email](mailto:henriquevicente@gmail.com), [Twitter](https://twitter.com/henriquev), [Flickr](http://www.flickr.com/photos/henriquev), [Linkedin](http://linkedin.com/in/henvic)).