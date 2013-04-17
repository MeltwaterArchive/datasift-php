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
 * This simple example demonstrates how to consume a stream using the stream
 * hash. You can pass multiple hashes to this script to consume multiple
 * streams through the same connection.
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

if ($_SERVER['argc'] < 2) {
	die("ERR: Please specify the stream hash to consume!\n\n");
}

/**
 * This class will handle the events
 */
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
   * @param string $hash The stream hash.
	 */
	public function onInteraction($consumer, $interaction, $hash)
	{
		if (!isset($interaction['interaction']['content'])) {
			$interaction['interaction']['content'] = 'No interaction.content for this interaction';
		}
		echo $hash.': '.$interaction['interaction']['content'].PHP_EOL.'--'.PHP_EOL;
	}

	/**
	 * Handle DELETE requests.
	 *
	 * @param DataSift_StreamConsumer $consumer The consumer object.
	 * @param array $interaction The interaction data.
   * @param string $hash The stream hash.
	 */
	public function onDeleted($consumer, $interaction, $hash)
	{
		echo 'DELETE request for interaction ' . $interaction['interaction']['id']
			. ' of type ' . $interaction['interaction']['type']
			. ' from stream ' . $hash . '. Please delete it from your archive.'
			. PHP_EOL.'--'.PHP_EOL;
	}

	/**
	 * Called when a status message is received.
	 *
	 * @param DataSift_StreamConsumer $consumer    The consumer sending the
	 *                                             event.
	 * @param string                  $type        The status type.
	 * @param array                   $info        The data sent with the
	 *                                             status message.
	 */
	public function onStatus($consumer, $type, $info)
	{
		switch ($type) {
			default:
				echo 'STATUS: '.$type.PHP_EOL;
				break;
		}
	}

	/**
	 * Called when a warning occurs or is received down the stream.
	 *
	 * @param DataSift_StreamConsumer $consumer The consumer object.
	 * @param string $message The warning message.
	 */
	public function onWarning($consumer, $message)
	{
		echo 'WARNING: '.$message.PHP_EOL.'--'.PHP_EOL;
	}

	/**
	 * Called when a error occurs or is received down the stream.
	 *
	 * @param DataSift_StreamConsumer $consumer The consumer object.
	 * @param string $message The error message.
	 */
	public function onError($consumer, $message)
	{
		echo 'ERROR: '.$message.PHP_EOL.'--'.PHP_EOL;
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

// Drop the script name from the command line arguments
array_shift($_SERVER['argv']);

// Authenticate
echo "Creating user...\n";
$user = new DataSift_User(USERNAME, API_KEY);

// Create the consumer
echo "Getting the consumer...\n";
$consumer = $user->getMultiConsumer(DataSift_StreamConsumer::TYPE_HTTP, $_SERVER['argv'], new EventHandler());

// And start consuming
echo "Consuming...\n--\n";
$consumer->consume();

// The consumer will never end
