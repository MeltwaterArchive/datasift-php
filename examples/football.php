<?php
/**
 * This example constructs a DataSift_Definition object with CSDL that looks
 * for anything containing the word "football". It then gets an HTTP
 * consumer for that definition and displays matching interactions to the
 * screen as they come in. It will display 10 interactions and then stop.
 *
 * NB: Most of the error handling (exception catching) has been removed for
 * the sake of simplicity. Nearly everything in this library may throw
 * exceptions, and production code should catch them. See the documentation
 * for full details.
 */

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

// Create the consumer
echo "Getting the consumer...\n";
$consumer = $definition->getConsumer(DataSift_StreamConsumer::TYPE_HTTP, 'display', 'stopped', 'processDeleteReq');

// And start consuming
echo "Consuming...\n--\n";
$consumer->consume();

echo "Finished consuming\n\n";

/**
 * Handle incoming data.
 *
 * @param DataSift_StreamConsumer $consumer The consumer object.
 * @param array $interaction The interaction data.
 */
function display($consumer, $interaction)
{
	static $num = 10;

	echo 'Type: '.$interaction['interaction']['type']."\n";
	echo 'Content: '.$interaction['interaction']['content']."\n--\n";

	// Stop after 10
	if ($num-- == 1) {
		echo "Stopping consumer...\n";
		$consumer->stop();
	}
}

/**
 * Handle DELETE requests.
 *
 * @param DataSift_StreamConsumer $consumer The consumer object.
 * @param array $interaction The interaction data.
 */
function processDeleteReq($consumer, $interaction)
{
	echo 'DELETE request for interaction ' . $interaction['interaction']['id']
		. ' of type ' . $interaction['interaction']['type']
		. '. Please delete it from your archive.';
}

/**
 * Called when the consumer has stopped.
 *
 * @param DataSift_StreamConsumer $consumer The consumer object.
 * @param string $reason The reason the consumer stopped.
 */
function stopped($consumer, $reason)
{
	echo "\nStopped: $reason\n\n";
}
