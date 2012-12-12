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
 * This example constructs a DataSift_Definition object with CSDL that looks
 * for anything containing the word "football". It then sits in a loop,
 * getting buffered interactions once every 10 seconds until it's retrieved
 * 10.
 *
 * NB: Most of the error handling (exception catching) has been removed for
 * the sake of simplicity. Nearly everything in this library may throw
 * exceptions, and production code should catch them. See the documentation
 * for full details.
 */
if (function_exists('date_default_timezone_set')) {
	date_default_timezone_set('UTC');
}

// Include the DataSift library
require dirname(__FILE__).'/../lib/datasift.php';

// Include the configuration - put your username and API key in this file
require dirname(__FILE__).'/../config.php';

// Authenticate
echo "Creating user...\n";
$user = new DataSift_User(USERNAME, API_KEY);

// Create the definition
$csdl = 'interaction.content contains "football"';
echo "Creating definition...\n  $csdl\n";
$definition = new DataSift_Definition($user, $csdl);

// Get buffered tweets until we've had 10
echo "Getting buffered interactions...\n--\n";
$num = 10;
$from_id = false;
do {
	$interactions = $definition->getBuffered($num, $from_id);
	foreach ($interactions as $interaction) {
		echo 'Type: '.$interaction['interaction']['type']."\n";
		echo 'Content: '.$interaction['interaction']['content']."\n--\n";
		$from_id = $interaction['interaction']['id'];
		$num--;
	}

	if ($num > 0) {
		// Sleep for 10 seconds before trying to get more
		echo "Sleeping...\n";
		sleep(10);
		echo "--\n";
	}
} while ($num > 0);

echo "Fetched 10 interactions, we're done.\n\n";
