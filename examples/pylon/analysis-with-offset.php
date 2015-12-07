<?php
/**
 * This simple example demonstrates how get a PYLON recording by it's ID,
 * then run a simple analysis query on that recording with a timezone offset
 *
 * @category  DataSift
 * @package   PHP-client
 * @author    Jason Dugdale <jason.dugdale@datasift.com>
 * @copyright 2105 MediaSift Ltd.
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      http://www.mediasift.com
 */

// Include the DataSift library
require dirname(__FILE__) . '/../../lib/datasift.php';

// Include the configuration - put your username and API key in this file
require dirname(__FILE__) . '/../../config.php';

if (function_exists('date_default_timezone_set')) {
  date_default_timezone_set('UTC');
}

// Authenticate
echo "Creating user...\n";
$user = new DataSift_User(USERNAME, API_KEY, false);

//Set your Analysis Query values
$hash = '<YOUR_RECORDING_HASH>'; // This must be the hash of an active recording
// This filter will likely get redacted; change to something related to your recording, or exclude
$filter = 'fb.content contains "some content"';
$start = mktime(0, 0, 0, date('n'), date('j') - 7); // 7 days ago
$end = mktime(0, 0, 0, date('n'), date('j')); // This morning

// Get the PYLON recording by hash
$pylon = DataSift_Pylon::fromHash($user, $hash);

//Set your Analysis Query parameters
$parameters = array(
    'analysis_type' => 'timeSeries',
    'parameters' => array(
        'interval' => 'day',
        'offset' => -5
    )
);

try {
    //Analyze the recording
    $analyze = $pylon->analyze($parameters, false, $start, $end, false);
} catch (Exception $e) {
    echo "Caught exception during analysis:\n";
    print_r($e);
}

echo json_encode($analyze, true);
