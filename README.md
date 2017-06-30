- - -

**_This project is no longer actively maintained. Proceed at your own risk!_**

- - -

DataSift PHP Client Library
===========================

[![Latest Version on Packagist](https://img.shields.io/packagist/v/datasift/datasift-php.svg?style=flat-square)](https://packagist.org/packages/datasift/datasift-php)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/datasift/datasift-php/master.svg?style=flat-square)](https://travis-ci.org/datasift/datasift-php)
[![Codecov](https://img.shields.io/codecov/c/github/datasift/datasift-php.svg?style=flat-square)](https://codecov.io/gh/datasift/datasift-php)

This is the official PHP library for accessing [Datasift](http://datasift.com/). See the examples
folder for some simple example usage.

## Getting Started

To get started choose one of our quick start guides:

* [STREAM Quick Start](http://dev.datasift.com/docs/products/stream/quick-start/getting-started-php)
* [PYLON for Facebook Topic Data Quick Start](http://dev.datasift.com/docs/products/pylon-fbtd/get-started/getting-started-php)


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

Requirements
------------

* PHP 5 with the cURL extension enabled and openssl for SSL support
* JSON (included in PHP 5.2+, otherwise use http://pecl.php.net/package/json)

The library will use SSL connections by default. While we recommend using SSL
you may disable it if required by passing false as the third parameter when
creating a user, or by calling $user->enableSSL(false) on the user object.

Testing
-------

To test the library itself, run the tests:

    composer test

Contributing
------------

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

Credits
-------

- [All Contributors](../../contributors)

License
-------

All code contained in this repository is Copyright 2011-2015 MediaSift Ltd.

This code is released under the MIT License (MIT). Please see [License File](LICENSE.md) for more information.
