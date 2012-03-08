DataSift PHP Client Library
===========================

This is the official PHP library for accessing [Datasift](http://datasift.com/). See the examples
folder for some simple example usage. Full documentation is in the doc folder.

The unit tests should be run with phpunit.

All examples and tests use the username and API key in config.php.

Simple example
--------------

This example looks for anything that contains the word "datasift" and simply
prints the content to the screen as they come in.

```php
<?php
	require 'lib/datasift.php';
	$user = new DataSift_User('your username', 'your api_key');
	$def = $user->createDefinition('interaction.content contains "datasift"');
	$consumer = $def->getConsumer(
		DataSift_StreamConsumer::TYPE_HTTP,
		function($consumer, $data) {
			echo $data['interaction']['content']."\n";
		}
	);
	$consumer->consume();
?>
```

See the DataSift documentation for full details of the data contained within
each interaction. See this page on our developer site for an example tweet:
http://dev.datasift.com/docs/targets/twitter/tweet-output-format

Requirements
------------

* PHP 5 with the cURL extension enabled
* JSON (included in PHP 5.2+, otherwise use http://pecl.php.net/package/json)

License
-------

All code contained in this repository is Copyright 2011 MediaSift Ltd.

This code is released under the BSD license. Please see the LICENSE file for
more details.

Changelog
---------

* v.1.3.0 Improved error handling (2012-03-08)

  Added onError and onWarning events - see examples/consumer-stream.php for an
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
