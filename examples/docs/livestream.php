<?php
include 'config.php';
/**
 * This example mimics the Twitter track functionality. Run the script with
 * any number of words or phrases as arguments and the script will create
 * the equivalent CSDL and consume it as a stream, displaying matching
 * interactions as they come in.
 *
 * NB: Most of the error handling (exception catching) has been removed for
 * the sake of simplicity. Nearly everything in this library may throw
 * exceptions, and production code should catch them. See the documentation
 * for full details.
 */
// Include the DataSift library
require '../../lib/datasift.php';

// Authenticate
echo "Creating user object...\n";
$user = new DataSift_User(USERNAME, API_KEY);

$words = array('music', 'mtv', 'itv', 'skyb2b', 'news', 'csi', 'criminal minds');
// Create the definition
$csdl = 'interaction.type == "twitter" and (interaction.content contains "' . implode('" or interaction.content contains "', $words) . '")';
echo 'The CSDL generated :' . $csdl;
echo "\nCreating definition...\n  $csdl\n";
$definition = new DataSift_Definition($user, $csdl);
// Create the consumer
echo "Getting the consumer...\n";
$consumer = $definition->getConsumer(DataSift_StreamConsumer::TYPE_HTTP, 'display', 'stopped');

// And start consuming
echo "Consuming...\n--\n";
$consumer->consume();

// The consumer will never end

/**
 * Handle incoming data.
 *
 * @param DataSift_StreamConsumer $consumer The consumer object
 * @param array $interaction The interaction data
 */
function display($consumer, $interaction)
{
	echo $interaction['interaction']['content'] . "\n--\n";
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
