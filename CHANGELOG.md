Changelog
=========================
## v.2.5.0
### Added
* Support for the [/pylon/sample API endpoint](http://dev.datasift.com/pylon/docs/api/pylon-api-endpoints/pylonsample)

### Changed
* Added PHP7 and HHVM to Travis

##v.2.4.1
###Fixed
* Fixed being able to search for identities by label

##v.2.4.0
###Added
* Support for Open Data Processing

##v.2.3.0
### Added
* ```The DataSift_Pylon::getAll()``` method now returns a raw response
* ```DataSift_Pylon::find``` and ```DataSift_Pylon::findAll``` methods added

##v.2.2.2
### Added
* Upgraded the API version to 1.2

### Removed
* Removed some obsolete files.

##v.2.2.0
### Added
* Support for PYLON
* CLI has been added for testing purposes along with some changes to the API client.

##v.2.1.8
### Added
* Support for ```pull```, ```historics/pause```, ```historics/resume```, ```source/add```, ```source/remove``` and ```source/log```.

##v.2.1.7 (2014-02-18)
###Added
* Composer support

###Deprecated
* Removed deprecated/redundant code from ApiClient

###Fixed
* Fixed live API tests

##v.2.1.6 (2013-04-24)
###Fixed
* Updated autoloader to use DIRECTORY_SEPARATOR for Windows interoperability

##v.2.1.5 (2013-04-17)
###Fixed
* Fixed minor typo in consume-stream.php example.

##v.2.1.4 (2013-03-05)
###Added
* Addeded support for new Historics field ```estimated_completion```.

* Made API requests default to using SSL.

* Modified Historics the 'sample' param.

##v.2.1.3
###Fixed
* Fixed reconnect issue from server-side disconnects.

##v.2.1.2 (2012-12-04)
###Fixed
* Fixed missing 'sample' param from ```historics/prepare``` calls.

##v.2.1.1
###Fixed
* Fixed issue #10 "Bug in api" by @salehsed

* Fixed syntax error in tests/testdata.php

##v.2.1.0 (2012-08-17)
###Added
* Added support for Historics and Push delivery.

##v.2.0.0
###Added
* Added SSL support for streams. (2012-06-18)
* Consumers no longer take functions for event handling. Instead you define a class that implements the ```DataSift_IStreamConsumerEventHandler``` interface and pass an instance of that.
* In addition to switching to an object-based event handler we have also introduced the following new events: ```onConnect```, ```onDisconnect``` and ```onStatus```.
* Added the develop branch as required by git flow (2012-05-24)

###Changed
* Changed event handling to an object instead of functions.
* SSL is enabled by default and can be disabled by passing false as the third parameter to the User constructor, or calling enableSSL(false) on the User object.

##v.1.3.0
### Added
* Added ```onError``` and ```onWarning``` events - see ```examples/consume-stream.php``` for an example.

### Changed
* Improved error handling (2012-03-08)
* Stopped the HTTP consumer from attempting to reconnect when it receives a 4xx response from the server.

##v.1.2.0
### Added
* Support for multiple streams (2012-02-29)

###Changed
* The User object now has a ```getMultiConsumer``` method which allows you to consume multiple streams through a single connection to DataSift. See the modified ```examples/consume-stream.php``` which can now take multiple hashes on the command line and uses this new feature to consume them.

NB: the callback functions will be called with an additional parameter which gives the hash of the stream that matched the interaction.
