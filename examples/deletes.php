<?php
if (function_exists('date_default_timezone_set')) {
	date_default_timezone_set('UTC');
}

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

// This class will handle the events
class EventHandler implements DataSift_IStreamConsumerEventHandler
{
	/**
	 * Called when the stream is connected.
	 *
	 * @param DataSift_StreamConsumer $consumer The consumer object.
	 */
	public function onConnect($consumer)
	{
		echo 'Connected'.PHP_EOL;
	}

	/**
	 * Handle incoming data.
	 *
	 * @param DataSift_StreamConsumer $consumer The consumer object.
	 * @param array $interaction The interaction data.
	 */
	public function onInteraction($consumer, $interaction, $hash)
	{
		echo '.';
	}

	/**
	 * Handle DELETE requests.
	 *
	 * @param DataSift_StreamConsumer $consumer The consumer object.
	 * @param array $interaction The interaction data.
	 */
	public function onDeleted($consumer, $interaction, $hash)
	{
		echo 'X';
	}

	/**
	 * Called when a warning occurs or is received down the stream.
	 *
	 * @param DataSift_StreamConsumer $consumer The consumer object.
	 * @param string $message The warning message.
	 */
	public function onWarning($consumer, $message)
	{
		echo 'WARNING: '.$message.PHP_EOL;
	}

	/**
	 * Called when a error occurs or is received down the stream.
	 *
	 * @param DataSift_StreamConsumer $consumer The consumer object.
	 * @param string $message The error message.
	 */
	public function onError($consumer, $message)
	{
		echo 'ERROR: '.$message.PHP_EOL;
	}

	/**
	 * Called when the stream is disconnected.
	 *
	 * @param DataSift_StreamConsumer $consumer The consumer object.
	 */
	public function onDisconnect($consumer)
	{
		echo 'Disconnected'.PHP_EOL;
	}

	/**
	 * Called when the consumer has stopped.
	 *
	 * @param DataSift_StreamConsumer $consumer The consumer object.
	 * @param string $reason The reason the consumer stopped.
	 */
	public function onStopped($consumer, $reason)
	{
		echo PHP_EOL.'Stopped: '.$reason.PHP_EOL.PHP_EOL;
	}
}

// Authenticate
echo "Creating user...\n";
$user = new DataSift_User(USERNAME, API_KEY);

// Create the definition
$csdl = 'interaction.type == "twitter" and interaction.sample < 1.0';
echo "Creating definition...\n  $csdl\n";
$definition = new DataSift_Definition($user, $csdl);

// Create the consumer
echo "Getting the consumer...\n";
$consumer = $definition->getConsumer(DataSift_StreamConsumer::TYPE_HTTP, new EventHandler());

// And start consuming
echo "Consuming...\n--\n";
$consumer->consume();

echo "Finished consuming\n\n";
