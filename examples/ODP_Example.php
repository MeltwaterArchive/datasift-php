<?php
 /**
 * This quick example demonstrates how to utilise the Ingestion endpoint
 *
 * @category  DataSift
 * @package   PHP-client
 * @author    Ryan Stanley <ryan.stanley@datasift.com>
 * @copyright 2015 MediaSift Ltd.
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      http://www.mediasift.com
 */

// Include the DataSift library
require dirname(_FILE_) . '/../lib/datasift.php';

// Include the configuration - put your username and API key in this file
require dirname(_FILE_) . '/../config.php';

// Autheticate 
echo "Creating user...\n";
$user = new DataSift_User('USER', 'API');

// Setting up the specific resources of the Managed Source
$ODPex = new stdClass();
$ODPex->parameters = new stdClass();
$ODPex->parameters->category = '6d2420bffa3d4fda9a85ccb47f626890_1';
$ODPex->parameters->idml = "web.link = id\r\nweb.content = body";

$resources = array($ODPex);

// Create new ODP Managed Source
$source = new DataSift_Source($user, array(
			'name' => 'My ODP Managed Source',
			'source_type' => 'custom',
			'resources' => $resources,
			));

try {
	$source->save();
} catch (Exception $e) {
	print_r($e->getMessage());
}

// Assign the Source ID to a vaiable
$source_id = $source->getId();

// Setting up a data set array to send to the endpoint
 $data = array(
 	array(
 		'id' => '234',
 		'body' => 'yo'
 	),
 	array(
 		'id' => '898',
 		'body' => 'hey'
 	)
 );

$data_set = "";

foreach ($data as $entry) {
	$data_set .= json_encode($entry) ."\n";
}

// Create the ODP object
$odp = new DataSift_ODP($user, $source_id);

// Use the ingest function and make an API request
$response = $odp->ingest($data_set);
var_dump($response);
?>
