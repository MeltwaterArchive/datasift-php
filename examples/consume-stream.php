<?php
/**
 * This simple example demonstrates how to consume a stream using the stream
 * hash. You can pass multiple hashes to this script to consume multiple
 * streams through the same connection.
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

if ($_SERVER['argc'] < 2) {
	die("ERR: Please specify the stream hash to consume!\n\n");
}

// Drop the script name from the command line arguments
array_shift($_SERVER['argv']);

// Authenticate
echo "Creating user...\n";
$user = new DataSift_User(USERNAME, API_KEY);

// Create the consumer
echo "Getting the consumer...\n";
$consumer = $user->getMultiConsumer(DataSift_StreamConsumer::TYPE_HTTP, $_SERVER['argv'], 'display', 'stopped', 'processDeleteReq', 'handleError', 'handleWarning');

// And start consuming
echo "Consuming...\n--\n";
$consumer->consume();

// The consumer will never end

/**
 * Handle incoming data.
 *
 * @param DataSift_StreamConsumer $consumer The consumer object
 * @param array $interaction The interaction data
 * @param string $hash The hash of the stream the matched this interaction.
 */
function display($consumer, $interaction, $hash)
{
	echo $hash.': '.$interaction['interaction']['content']."\n--\n";
}

/**
 * Handle DELETE requests.
 *
 * @param DataSift_StreamConsumer $consumer The consumer object.
 * @param array $interaction The interaction data.
 * @param string $hash The hash of the stream the matched this interaction.
 */
function processDeleteReq($consumer, $interaction, $hash)
{
	echo 'DELETE request for interaction ' . $interaction['interaction']['id']
		. ' of type ' . $interaction['interaction']['type']
		. ' from stream ' . $hash . '. Please delete it from your archive.'."\n--\n";
}

/**
 * Called when the consumer has stopped. In this example this should never
 * be called.
 *
 * @param DataSift_StreamConsumer $consumer The consumer object
 */
function stopped($consumer, $reason)
{
	echo "\nStopped: $reason\n\n";
}

/**
 * Called when an error message is received.
 *
 * @param DataSift_StreamConsumer $consumer The consumer object.
 * @param string $message The message.
 */
function handleError($consumer, $message)
{
	echo "ERROR: $message\n--\n";
}

/**
 * Called when a warning message is received.
 *
 * @param DataSift_StreamConsumer $consumer The consumer object.
 * @param string $message The message.
 */
function handleWarning($consumer, $message)
{
	echo "WARNING: $message\n--\n";
}
