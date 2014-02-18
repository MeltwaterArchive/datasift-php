DataSift PHP Client Library
===========================

This is the official PHP library for accessing [Datasift](http://datasift.com/). See the examples
folder for some simple example usage.

The unit tests should be run with phpunit.

All examples and tests use the username and API key in config.php.

Note that we use [git flow](https://github.com/nvie/gitflow) to manage development.

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
each interaction. See this page on our developer site for an example tweet:
http://dev.datasift.com/docs/targets/twitter/tweet-output-format

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

Changelog
---------
* v.2.1.7 Added Composer support; removed deprecated/redundant code from ApiClient; fixed live API tests (2014-02-18)

* v.2.1.6 Updated autoloader to use DIRECTORY_SEPARATOR for Windows interoperability (2013-04-24)

* v.2.1.5 Fixed minor typo in consume-stream.php example. (2013-04-17)

* v.2.1.4 Added support for new Historics field 'estimated_completion'. 
  
  Made API requests default to using SSL.
  
  Modified Historics the 'sample' param. (2013-03-05)

* v.2.1.3 Fixed reconnect issue from server-side disconnects.

* v.2.1.2 Fixed missing 'sample' param from /historics/prepare calls. (2012-12-04)

* v.2.1.1 Fixed issue #10 "Bug in api" by @salehsed

  Fixed syntax error in tests/testdata.php

* v.2.1.0 Added support for Historics and Push delivery. (2012-08-17)

* v.2.0.0 Changed event handling to an object instead of functions. Added SSL
          support for streams. (2012-06-18)

  Consumers no longer take functions for event handling. Instead you define a
  class that implements the DataSift_IStreamConsumerEventHandler interface and
  pass an instance of that. In addition to switching to an object-based event
  handler we have also introduced the following new events: onConnect,
  onDisconnect and onStatus.

  SSL is enabled by default and can be disabled by passing false as the third
  parameter to the User constructor, or calling enableSSL(false) on the User
  object.

* Added the develop branch as required by git flow (2012-05-24)

* v.1.3.0 Improved error handling (2012-03-08)

  Added onError and onWarning events - see examples/consume-stream.php for an
  example.

  Stopped the HTTP consumer from attempting to reconnect when it receives a
  4xx response from the server.

* v.1.2.0 Support for multiple streams (2012-02-29)

  The User object now has a getMultiConsumer method which allows you to
  consume multiple streams through a single connection to DataSift. See the
  modified examples/consume-stream.php which can now take multiple hashes on
  the command line and uses this new feature to consume them.

  NB: the callback functions will be called with an additional parameter which
  gives the hash of the stream that matched the interaction.

* v.1.1.0 Twitter Compliance (2012-02-23)

  The consumer now has an onDeleted method to which you can assign a block
  that will be called to handle DELETE requests from Twitter. See deletes.php
  in the examples folder for a sample implementation.
  (@see http://dev.datasift.com/docs/twitter-deletes)

  NB: if you are storing tweets you must implement this method in your code
  and take appropriate action to maintain compliance with the Twitter license.
