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
 * for anything containing the word "football". It then gets an HTTP
 * consumer for that definition and displays matching interactions to the
 * screen as they come in. It will display 10 interactions and then stop.
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

/**
 * This class will handle the events.
 */
class EventHandler implements DataSift_IStreamConsumerEventHandler
{
  /**
   * @var int Timeout in seconds.
   */
	private $_num = 10;

	/**
	 * Called when the stream is connected.
	 *
	 * @param DataSift_StreamConsumer $consumer The consumer sending the event.
	 *
	 * @return void
	 */
	public function onConnect($consumer)
	{
		echo 'Connected'.PHP_EOL;
	}

	/**
	 * Called for each interaction consumed.
	 *
	 * @param DataSift_StreamConsumer $consumer    The consumer sending the
	 *                                             event.
	 * @param array                   $interaction The interaction data.
	 * @param string                  $hash        The hash of the stream that
	 *                                             matched this interaction.
	 *
	 * @return void
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
	 * Called for each deletion notification consumed.
	 *
	 * @param DataSift_StreamConsumer $consumer    The consumer sending the
	 *                                             event.
	 * @param array                   $interaction The interaction data.
	 * @param string                  $hash        The hash of the stream that
	 *                                             matched this interaction.
	 *
	 * @return void
	 */
	public function onDeleted($consumer, $interaction, $hash)
	{
		echo 'DELETE request for interaction ' . $interaction['interaction']['id']
			. ' of type ' . $interaction['interaction']['type']
			. '. Please delete it from your archive.';
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
	 * @param DataSift_StreamConsumer $consumer The consumer sending the event.
	 * @param string $message The warning message.
	 *
	 * @return void
	 */
	public function onWarning($consumer, $message)
	{
		echo 'WARNING: '.$message.PHP_EOL;
	}

	/**
	 * Called when an error occurs or is received down the stream.
	 *
	 * @param DataSift_StreamConsumer consumer The consumer sending the event.
	 * @param string $message The error message.
	 *
	 * @return void
	 */
	public function onError($consumer, $message)
	{
		echo 'ERROR: '.$message.PHP_EOL;
	}

	/**
	 * Called when the stream is disconnected.
	 *
	 * @param DataSift_StreamConsumer $consumer The consumer sending the event.
	 *
	 * @return void
	 */
	public function onDisconnect($consumer)
	{
		echo 'Disconnected'.PHP_EOL;
	}

	/**
	 * Called when the consumer stops for some reason.
	 *
	 * @param DataSift_StreamConsumer $consumer The consumer sending the event.
	 * @param string $reason The reason the consumer stopped.
	 *
	 * @return void
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
