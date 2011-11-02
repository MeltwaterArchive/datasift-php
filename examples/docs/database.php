<?php

include 'config.php';
//because a live stream never ends we need to ensure PHP doesn't kill our script if the max execution time has a predefined limit
set_time_limit(0); //0 means no time limit
/**
 * Demonstrates connecting to the live stream and  putting data into a mysql database for use later
 */
// Include the DataSift library
require '../../lib/datasift.php';

/**
 * Set up our MySQL connection details
 */
$link = mysql_connect('localhost', 'root', '');
if (!$link) {
	die('Could not connect: ' . mysql_error());
}
$db = mysql_select_db('courtney0', $link);

if (!$db) {
	die('Can\'t use foo : ' . mysql_error());
}
// Authenticate
echo "Creating user object...\n";
$user = new DataSift_User(USERNAME, API_KEY);

$words = array('music', 'mtv', 'itv', 'skyb2b', 'news', 'csi', 'criminal minds');
// Create the definition
$csdl = 'interaction.type == "twitter" and (interaction.content contains "' . implode('" or interaction.content contains "', $words) . '")';

$definition = new DataSift_Definition($user, $csdl);
// Create the consumer
$consumer = $definition->getConsumer(DataSift_StreamConsumer::TYPE_HTTP, 'display', 'stopped');

// And start consuming
$consumer->consume();

//The consumer will never end

/**
 * Handle incoming data.
 *
 * @param DataSift_StreamConsumer $consumer The consumer object
 * @param array $interaction The interaction data
 */
function display($consumer, $interaction)
{
	$sentiment = isset($interaction['salience']['content']['sentiment']) ? $interaction['salience']['content']['sentiment'] : '';
	$lang = isset($interaction['language']['tag']) ? $interaction['language']['tag'] : '';
	$pscore = isset($interaction['peerindex']['score']) ? $interaction['peerindex']['score'] : '';
	$auth = isset($interaction['peerindex']['authority']) ? $interaction['peerindex']['authority'] : '';
	$kscore = isset($interaction['klout']['score']) ? $interaction['klout']['score'] : '';
	$kclass = isset($interaction['klout']['class']) ? $interaction['klout']['class'] : '';

	$query = "INSERT INTO datasiftStreamExample (id,content,source,username,name,type,link,created_at,sentiment,language,peerindex_score,peerindex_authority,klout_score,klout_class)
			VALUES ('" . $interaction['interaction']['id']
			. "','" . $interaction['interaction']['content']
			. "','" . $interaction['interaction']['source']
			. "','" . $interaction['interaction']['author']['username']
			. "','" . $interaction['interaction']['author']['name']
			. "','" . $interaction['interaction']['type']
			. "','" . $interaction['interaction']['link']
			. "','" . $interaction['interaction']['created_at']
			. "','" . $sentiment
			. "','" . $lang
			. "','" . $pscore
			. "','" . $auth
			. "','" . $kscore
			. "','" . $kclass
			. "');";
	mysql_query($query);
	print $interaction['interaction']['content'] . '<br />';
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

//close our mysql connection
mysql_close($link);