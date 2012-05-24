<?php
if (function_exists('date_default_timezone_set')) {
	date_default_timezone_set('UTC');
}

/**
 * This example will prepare and consume a historics job.
 *
 * Usage: php historic-dump.php
 * (CLI args may be added later!)
 */

// Steve Jobs died at around 3pm at his home. We want to grab anything mentioning
// him or apple from around that time (UTC) for 24 hours.
$csdl = 'interaction.type == "twitter"';
$from = 1336989600; //strtotime('2012-05-23 12:00:00');
$to = $from + (3600);

// Include the DataSift library
require dirname(__FILE__).'/../lib/datasift.php';

// Include the configuration - put your username and API key in this file
require dirname(__FILE__).'/../config.php';

// Authenticate
echo "Creating user...\n";
$user = new DataSift_User(USERNAME, API_KEY);

// Create the definition
echo "Creating definition...\n  $csdl\n";
$definition = $user->createDefinition($csdl);

// Create the historic query
$historic = $definition->createHistoric($from, $to, array('twitter'), 'API Test');
echo 'Historic playback ID: '.$historic->getHash()."\n";

// Create the consumer
echo "Getting the consumer...\n";
$consumer = $historic->getConsumer(DataSift_StreamConsumer::TYPE_HTTP, 'display', 'stopped', 'processDeleteReq');

// Create the DB
$db = sqlite_open(dirname(__FILE__).'/historic-dump.db', 0666, $sqlite_error);
if (!$db) {
	$consumer->stop();
	die('Failed to open sqlite DB: '.$sqlite_error);
}
@sqlite_query($db, 'drop table tweets');
sqlite_query($db, 'create table tweets (id integer, ts integer, who varchar(20), what varchar(250))');

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
	global $db;

	$sql =
		'insert into tweets (id, ts, what) values ('.
		'"'.sqlite_escape_string($interaction['interaction']['id']).'", '.
		'"'.sqlite_escape_string(strtotime($interaction['interaction']['created_at'])).'", '.
		'"'.sqlite_escape_string($interaction['interaction']['content']).'"'.
		')';

	if (sqlite_query($db, $sql)) {
		echo '.';
	} else {
		echo '-';
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
	global $db;

	$sql = 'delete from tweets where id = "'.sqlite_escape_string($interaction['interaction']['id']).'"';

	if (sqlite_query($db, $sql)) {
		echo 'x';
	} else {
		echo 'X';
	}
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
