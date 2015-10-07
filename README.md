DataSift PHP Client Library
===========================

[![Build Status](https://api.travis-ci.org/datasift/datasift-php.svg)](https://travis-ci.org/datasift/datasift-php)

This is the official PHP library for accessing [Datasift](http://datasift.com/). See the examples
folder for some simple example usage.


Getting Started
---------------

**Read our [PHP Getting Started Guide](http://dev.datasift.com/quickstart/php) to get started with the DataSift platform.** This guide will take you through creating a [DataSift](http://datasift.com) account, and activating data sources which you will need to do before using the DataSift API.

Many of the examples and API endpoints used in this library require you have enabled certain data sources before you can receive any data (you should do this at [datasift.com/source](https://datasift.com/source)). Certain API features, such as [Historics](http://datasift.com/platform/historics/) and [Managed Sources](http://datasift.com/platform/datasources/) will require you have signed up to a monthly subscription before you can access them.

If you are interested in using these features, or would like more information about DataSift, please [get in touch](http://datasift.com/contact-us/)!


Simple example
--------------

This example looks for anything that contains the word "datasift" and simply
prints the content to the screen as they come in.

```php
<?php
  // Load the library (If you're using this library standalone)
  require 'lib/datasift.php';
  // Load the library via Composer
  //require '/path/to/vendor/autoload.php';

  // An object of this type will receive events
  class EventHandler implements DataSift_IStreamConsumerEventHandler
  {
    public function onInteraction($consumer, $interaction, $hash)
    {
      echo $interaction['interaction']['content']."\n";
    }

    // Ignore the other events for the purposes of this example.
    public function onConnect($consumer)                      { }
    public function onDeleted($consumer, $interaction, $hash) { }
    public function onStatus($consumer, $type, $info)         { }
    public function onWarning($consumer, $message)            { }
    public function onError($consumer, $message)              { }
    public function onDisconnect($consumer)                   { }
    public function onStopped($consumer, $reason)             { }
  }
  // Create the user
  $user = new DataSift_User('your username', 'your api_key');
  // Create a definition looking for the word "datasift"
  $def = $user->createDefinition('interaction.content contains "datasift"');
  // Get an HTTP stream consumer for that definition
  $consumer = $def->getConsumer(DataSift_StreamConsumer::TYPE_HTTP, new EventHandler());
  // Consume it - this will not return unless the stream gets disconnected
  $consumer->consume();
?>
```

See the DataSift documentation for full details of the data contained within
each interaction. See this page on our developer site for an example interaction:
http://dev.datasift.com/docs/targets/interaction/mapping


Contributing
------------

Please feel free to contribute to this repository using pull requests.

The unit tests should be run with phpunit.

Note that we use [git flow](https://github.com/nvie/gitflow) to manage development.


Requirements
------------

* PHP 5 with the cURL extension enabled and openssl for SSL support
* JSON (included in PHP 5.2+, otherwise use http://pecl.php.net/package/json)

The library will use SSL connections by default. While we recommend using SSL
you may disable it if required by passing false as the third parameter when
creating a user, or by calling $user->enableSSL(false) on the user object.

License
-------

All code contained in this repository is Copyright 2011-2012 MediaSift Ltd.

This code is released under the BSD license. Please see the LICENSE file for
more details.

