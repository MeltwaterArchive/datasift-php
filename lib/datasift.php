<?php
/**
 * DataSift client
 *
 * This software is the intellectual property of MediaSift Ltd., and is covered
 * by retained intellectual property rights, including copyright.
 *
 * @category  DataSift
 * @package   PHP-client
 * @author    Stuart Dallas <stuart@3ft9.com>
 * @copyright 2011 MediaSift Ltd.
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      http://www.mediasift.com
 */

/**
* This is the base file for the DataSift API Library. This is the only file
* you need to include to use the library.
*/

// Set up the class autoloader. If it's available we use
// spl_autoload_register, if not we load all of the classes now.
if (function_exists('spl_autoload_register')) {
	assert(spl_autoload_register('datasift_autoloader'));
} else {
	$datasift_classes = array(
		'DataSift_Exception_AccessDenied',
		'DataSift_Exception_APIError',
		'DataSift_Exception_CompileFailed',
		'DataSift_Exception_InvalidData',
		'DataSift_Exception_NotYetImplemented',
		'DataSift_Exception_RateLimitExceeded',
		'DataSift_Exception_StreamError',
		'DataSift_Push_Definition',
		'DataSift_Push_LogEntry',
		'DataSift_Push_Subscription',
		'DataSift_StreamConsumer_HTTP',
		'DataSift_ApiClient',
		'DataSift_Definition',
		'DataSift_Historic',
		'DataSift_IStreamConsumerEventHandler',
		'DataSift_StreamConsumer',
		'DataSift_User',
	);

	foreach ($datasift_classes as $class) {
		assert(datasift_autoloader($class));
	}
}

/**
 * Class autoloader.
 *
 * @param string $classname The name of the class to load.
 *
 * @return bool True if the class was successfully loaded.
 */
function datasift_autoloader($classname)
{
	static $libdir    = false;
	static $libdirlen = false;
	if ($libdir === false) {
		$libdir    = realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR;
		$libdirlen = strlen($libdir);
	}

	$retval = false;

	if (substr($classname, 0, 9) == 'DataSift_') {
		// The realpath function will return false if the file does not exist
		$filename = realpath($libdir.str_replace('_', DIRECTORY_SEPARATOR, $classname).'.php');
		if ($filename !== false) {
			assert($libdir == substr($filename, 0, $libdirlen));
			include $filename;
			$retval = true;
		}
	}

	return $retval;
}
