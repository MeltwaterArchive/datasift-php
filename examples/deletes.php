<?php
/**
 * This example consumes 1% of the Twitter stream and outputs a . for each
 * interaction received, and an X for each delete notification.
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
$csdl = 'interaction.type == "twitter" and interaction.sample < 1.0';
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
	echo '.';
}

/**
 * Handle DELETE requests.
 *
 * @param DataSift_StreamConsumer $consumer The consumer object.
 * @param array $interaction The interaction data.
 */
function processDeleteReq($consumer, $interaction)
{
	echo 'X';
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
