<?php
/**
 * This simple example demonstrates how to use the Pylon Sample API endpoint
 *
 * @category  DataSift
 * @package   PHP-client
 * @author    Ryan Stanley <ryan.stanley@datasift.com>
 * @copyright 2015 MediaSift Ltd.
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      http://www.mediasift.com
 */

// Include the DataSift library
require dirname(__FILE__) . '/../../lib/datasift.php';

// Include the configuration - put your username and API key in this file
require dirname(__FILE__) . '/../../config.php';

// Authenticate
echo "Creating user...\n";
$user = new DataSift_User(USERNAME, API_KEY, false);

$filter = '(fb.content any "coffee" OR fb.hashtags in "tea") AND fb.language in "en"';

//Validate the CSDL
$validate = DataSift_Pylon::validate($user, $filter);

echo "Definition has been successfully validated, DPUs: {$validate['dpu']} created at: {$validate['created_at']} \n\n";

//Create the PYLON object and manually enter a Pylon Hash to use in the sample; you'll need to
// replace the hash below with a real PYLON hash from your own DataSift account
$pylon = new DataSift_Pylon($user, array('hash' => '1a4268c9b924d2c48ed1946d6a7e6288'));

// Setting up parameters for the /pylon/sample request
$start = 1445209200;
$end = 1445274000;
$count = 10;

$sample = $pylon->sample($filter, $start, $end, $count);

echo "Sample created successfully"
