<?php
if (function_exists('date_default_timezone_set')) {
	date_default_timezone_set('UTC');
}

/**
 * This example will prepare and consume a historics job, writing the
 * interactions received to a CSV file.
 */

// Edit these to set the parameters of the historics query
$csdl = 'interaction.type == "twitter"';
$from = strtotime('2012-05-23 12:00:00');
$to = $from + 3600; // 1 hour
$csv_filename = dirname(__FILE__).'/historic-dump.csv';
// End of configuration

// Include the DataSift library
require dirname(__FILE__).'/../lib/datasift.php';

// Include the configuration - put your username and API key in this file
require dirname(__FILE__).'/../config.php';

// This class will handle the events
class EventHandler implements DataSift_IStreamConsumerEventHandler
{
	private $_outfp = null;
	private $_counter = 0;

	public function __construct($outfp)
	{
		$this->_outfp = $outfp;
	}

	/**
	 * Called when the stream is connected.
	 *
	 * @param DataSift_StreamConsumer $consumer The consumer object.
	 */
	public function onConnect($consumer)
	{
		echo "Connected\n";
	}

	/**
	 * Handle incoming data.
	 *
	 * @param DataSift_StreamConsumer $consumer The consumer object.
	 * @param array $interaction The interaction data.
	 */
	public function onInteraction($consumer, $interaction, $hash)
	{
		$res = fputcsv($this->_outfp, array(
			$interaction['interaction']['id'],
			$interaction['interaction']['created_at'],
			empty($interaction['interaction']['author']['username'])
				? '' : $interaction['interaction']['author']['username'],
			empty($interaction['interaction']['content'])
				? '' : $interaction['interaction']['content'],
		));

		// Provide some visual feedback
		if ($res) {
			echo '.';
		} else {
			echo '-';
		}

		// Flush the file buffer every 100 interactions
		$this->_counter++;
		if ($this->_counter >= 100) {
			fflush($this->_outfp);
			$this->_counter = 0;
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
		// For the purposes of this example we do nothing with deletes, but in
		// your implementation you will need to properly process deletes by
		// removing them from your storage system.
	}

	/**
	 * Called when a warning occurs or is received down the stream.
	 *
	 * @param DataSift_StreamConsumer $consumer The consumer object.
	 * @param string $message The warning message.
	 */
	public function onWarning($consumer, $message)
	{
		echo 'WARN: '.$message.PHP_EOL;
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
		echo "Disconnected\n";
	}

	/**
	 * Called when the consumer has stopped.
	 *
	 * @param DataSift_StreamConsumer $consumer The consumer object.
	 * @param string $reason The reason the consumer stopped.
	 */
	public function onStopped($consumer, $reason)
	{
		echo "\nStopped: $reason\n\n";
	}
}

// Authenticate
echo "Creating user...\n";
$user = new DataSift_User(USERNAME, API_KEY);

// Create the definition
echo "Creating definition...\n  $csdl\n";
$definition = $user->createDefinition($csdl);

// Create the historic query
$historic = $definition->createHistoric($from, $to, array('twitter'), 'API Test');
echo 'Historic playback ID: '.$historic->getHash()."\n";

// Attach output types to the historic - first arg to the type constructors is the delivery frequency
// $historic->addOutputType(new DataSift_OutputType_HTTP(60, 'url', 'username', 'password'));
// $historic->addOutputType(new DataSift_OutputType_S3(3600, 'username', 'api_key', 'bucket'));
// $historic->addOutputType(new DataSift_OutputType_FTP(86400, 'hostname', 'username', 'password', 'remote_filename'));

// Create the output file
$outfp = fopen($csv_filename, 'wt');
if (!$outfp) {
	die('Failed to open the CSV output file: '.$outfp.PHP_EOL);
}
fputcsv($outfp, array('ID', 'Created At', 'Username', 'Content'));

// Create the consumer
echo "Getting the consumer...\n";
$consumer = $historic->getConsumer(DataSift_StreamConsumer::TYPE_HTTP, new EventHandler($outfp));

// And start consuming
echo "Consuming...\n--\n";
$consumer->consume();

echo "Finished consuming\n\n";
