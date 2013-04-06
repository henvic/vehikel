#!/bin/sh
#this installs additional dependencies not available not available on npm or

install() {
  if [ -f vendor/lock ]
    then
      echo "Remove the ./vendor/lock file after cleaning up" >&2
      echo "./vendor and ./static/vendor directories of" >&2
      echo "old dependencies before continuing." >&2
      echo "If you don't do that the install procedure will almost certainly fail." >&2
      exit 1
  fi

  touch vendor/lock

  echo "Beginning to install ./static/vendor dependencies"
  cd static/vendor

  # Install Backbone.js
  echo "Downloading Backbone.js"
  mkdir backbone-0.9.2
  cd backbone-0.9.2
  curl -o backbone.js https://raw.github.com/documentcloud/backbone/0.9.2/backbone.js
  cd ..

  # Download Twitter Bootstrap
  echo "Downloading Twitter Bootstrap"
  curl -o bootstrap.v2.3.1.zip -O https://raw.github.com/twitter/bootstrap/5652c354ea4c09dc5ee724791e30d3f60cec7f3e/assets/bootstrap.zip

  # Download elements
  echo "Downloading elements"
  mkdir elements
  cd elements
  curl -o elements.less -O https://raw.github.com/dmitryf/elements/3df7be1ea5b3c284efc19361d1abf9899ae310b1/elements.less
  cd ..

  # Download jQuery
  echo "Downloading jQuery"
  mkdir jquery-1.9.1
  cd jquery-1.9.1
  curl -o jquery.js -O http://code.jquery.com/jquery-1.9.1.js
  cd ..

  # Download jQuery.fn.autoResize-1.14
  echo "Downloading jQuery.fn.autoResize-1.14"
  mkdir jquery-fn-autoResize-1.14
  cd jquery-fn-autoResize-1.14
  curl -o henvic-jquery.fn.autoResize-1.14.zip -O https://nodeload.github.com/henvic/jQuery.fn.autoResize/zipball/master
  cd ..

  # Download jQuery-tablesorter
  echo "Downloading jQuery-tablesorter"
  mkdir jquery-tablesorter-2.0.5b
  cd jquery-tablesorter-2.0.5b
  curl -o tablesorter.zip -O http://tablesorter.com/__jquery.tablesorter.zip
  cd ..

  # Download Modernizr
  echo "Downloading Modernizr"
  mkdir modernizr-2.6.1
  cd modernizr-2.6.1
  curl -o modernizr.js -O https://raw.github.com/Modernizr/Modernizr/1cf3c14ee02ae2e88291fc0b63bc2aefe38fc93e/modernizr.js
  cd ..

  # Download QUnit
  echo "Downloading QUnit"
  mkdir qunit-1.9.0
  cd qunit-1.9.0
  curl -o qunit.js -O http://code.jquery.com/qunit/qunit-1.9.0.js
  curl -o qunit.css -O http://code.jquery.com/qunit/qunit-1.9.0.css
  cd ..

  # Download RequireJS
  echo "Downloading RequireJS"
  mkdir require-2.0.5
  cd require-2.0.5
  curl -o require.js -O http://requirejs.org/docs/release/2.0.5/comments/require.js
  curl -o text-2.0.3.js -O https://raw.github.com/requirejs/text/fffccb414d6a0a0c39b00f23997fdfa0955df7c1/text.js
  cd ..

  # Download Underscore.js
  echo "Downloading Underscore.js"
  mkdir underscore-1.3.3
  cd underscore-1.3.3
  curl -o underscore.js -O https://raw.github.com/documentcloud/underscore/1.3.3/underscore.js
  cd ..

  echo "Don't install anything directly here. It will be erased with any new install. Use bin/install for the job instead." >> IMPORTANT.md

  md5sum -c files.md5
  if [ ! $? -eq 0 ]
    then
      echo "Install failed: wrong checksum." >&2
      exit 1
  fi

  echo "Checksum tests for static/vendor downloads passed."

  echo "Uncompressing / Installing some libraries"

  unzip bootstrap.v2.3.1.zip
  mv bootstrap bootstrap-2.3.1
  rm bootstrap.v2.3.1.zip

  cd jquery-fn-autoResize-1.14/
  unzip henvic-jquery.fn.autoResize-1.14.zip henvic-jQuery.fn.autoResize-653c1e7/jquery.autoresize.js
  cp henvic-jQuery.fn.autoResize-653c1e7/jquery.autoresize.js .
  rm -r henvic-jQuery.fn.autoResize-653c1e7/
  cd ..

  cd jquery-tablesorter-2.0.5b
  unzip tablesorter.zip jquery.metadata.js jquery.tablesorter.js
  rm tablesorter.zip
  cd ..

  echo "./static/vendor installed\n\n"

  cd ../..

  echo "Beginning to install ./vendor dependencies"
  cd vendor

  echo "Downloading Symfony"
  curl -o symfony-2.0.16.tar.gz -O https://nodeload.github.com/symfony/symfony/tarball/v2.0.16

  echo "Downloading ZendFramework"
  curl -o ZendFramework-1.12.3-minimal.tar.gz -O \
  http://framework.zend.com/releases/ZendFramework-1.12.3/ZendFramework-1.12.3-minimal.tar.gz

  echo "Downloading zend-form-decorators-bootstrap"
  curl -o twitter.bootstrap.tar.gz -O \
  https://nodeload.github.com/Emagister/zend-form-decorators-bootstrap/tarball/0.1.3

  echo "Downloading HTML Purifier (standalone version)"
  curl -o htmlpurifier-4.4.0-standalone.tar.gz -O \
  http://htmlpurifier.org/releases/htmlpurifier-4.4.0-standalone.tar.gz

  echo "Downloading oauth-php"
  curl -o oauth-php-175.tar.gz -O http://oauth-php.googlecode.com/files/oauth-php-175.tar.gz

  echo "Downloading predis"
  curl -o predis-0.7.tar.gz -O https://nodeload.github.com/nrk/predis/tarball/v0.7

  echo "Don't install anything directly here. It will be erased with any new install. Use bin/install for the job instead." >> IMPORTANT.md

  md5sum -c files.md5
  if [ ! $? -eq 0 ]
    then
      echo "Install failed: wrong checksum." >&2
      exit 1
  fi

  echo "Checksum tests for static/vendor downloads passed."

  echo "Uncompressing / Installing some libraries"

  tar xzf symfony-2.0.16.tar.gz
  mv symfony-symfony-3b696f7/src/Symfony Symfony
  rm -r symfony-symfony-3b696f7

  tar xzf ZendFramework-1.12.3-minimal.tar.gz
  mv ZendFramework-1.12.3-minimal/library/Zend .
  rm -r ZendFramework-1.12.3-minimal
  rm ZendFramework-1.12.3-minimal.tar.gz

  tar xzf twitter.bootstrap.tar.gz
  mv Emagister-zend-form-decorators-bootstrap-78f94a6/Twitter .
  rm -r Emagister-zend-form-decorators-bootstrap-78f94a6
  rm twitter.bootstrap.tar.gz

  tar xzf htmlpurifier-4.4.0-standalone.tar.gz
  mv htmlpurifier-4.4.0-standalone htmlpurifier-standalone
  rm htmlpurifier-4.4.0-standalone.tar.gz

  tar xzf oauth-php-175.tar.gz
  rm oauth-php-175.tar.gz

  tar xzf predis-0.7.tar.gz
  mv nrk-predis-6e9db69 predis
  rm predis-0.7.tar.gz

  cd ..
  composer install

  echo "./vendor installed\n"

  echo "vendors install for PHP / JS client-side finished."
  echo "now use npm install to install the node dependencies."
  exit 0
}

# make sure the script runs on the proper working directory
DIRECTORY=`dirname $0`/..
cd $DIRECTORY

install
