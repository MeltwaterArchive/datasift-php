DataSift PHP Client Library
===========================

This is the official PHP library for accessing [Datasift](http://datasift.com/). See the examples folder for some simple example usage. Full documentation is in the doc folder.

The unit tests should be run with phpunit.

All examples and tests use the username and API key in config.php.

Simple example
--------------

This example looks for anything that contains the word "datasift" and simply prints the content to the screen as they come in.

```php
<?php
	require 'lib/datasift.php';
	$user = new DataSift_User('your username', 'your api_key');
	$def = $user->createDefinition('interaction.content contains "datasift"');
	$consumer = $def->getConsumer(DataSift_StreamConsumer::TYPE_HTTP,
	function($consumer, $data) {
	echo $data['interaction']['content']."\n";
	}
	);
	$consumer->consume();
?>
```

See the DataSift documentation for full details of the data contained within each interaction. See this page on our developer site for an example tweet: http://dev.datasift.com/docs/targets/twitter/tweet-output-format


Requirements
------------

* PHP 5
* JSON (included in PHP 5.2+, otherwise use http://pecl.php.net/package/json)


License
-------

All code contained in this repository is Copyright 2011 MediaSift Ltd.

This code is released under the BSD license. Please see the LICENSE file for more details.
