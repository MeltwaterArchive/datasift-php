<?php
if (function_exists('date_default_timezone_set')) {
	date_default_timezone_set('UTC');
}

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

// This class will handle the events
class EventHandler implements DataSift_IStreamConsumerEventHandler
{
	private $_num = 10;

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
		echo 'Type: '.$interaction['interaction']['type']."\n";
		echo 'Content: '.$interaction['interaction']['content']."\n--\n";

		// Stop after 10
		if ($this->_num-- == 1) {
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
	public function onDeleted($consumer, $interaction, $hash)
	{
		echo 'DELETE request for interaction ' . $interaction['interaction']['id']
			. ' of type ' . $interaction['interaction']['type']
			. '. Please delete it from your archive.';
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
$csdl = 'interaction.content contains "football"';
echo "Creating definition...\n  $csdl\n";
$definition = new DataSift_Definition($user, $csdl);

// Create the consumer
echo "Getting the consumer...\n";
$consumer = $definition->getConsumer(DataSift_StreamConsumer::TYPE_HTTP, new EventHandler());

// And start consuming
echo "Consuming...\n--\n";
$consumer->consume();

echo "Finished consuming\n\n";
