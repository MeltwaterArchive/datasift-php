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
 $user = new DataSift_User(USERNAME, API_KEY);

// Create new ODP Managed Source
$source = new DataSift_Source($user, array(
			'name' => 'My ODP Managed Source',
			'source_type' => 'custom',
			'resources' => 'parameters' = array('category' => '6d2420bffa3d4fda9a85ccb47f626890_1', 'idml' => 'web.link = id\r\nweb.content = body')
			'auth' => 'USER:API'
			));

// Assign the Source ID to a vaiable
$source_id = $source->getId();

// Preapre data to be sent to Ingestion Endpoint
// $data_set = 'cat /tmp/my_private_data.json';

 $data_set = array(
 	array('id' => '234',
 			'body' => 'yo'), array(
 			'id' => '898',
 			'body' => 'hey'));

 // Create the ODP object
 $odp = new DataSift_ODP($user, $source_id, $data_set);
 //$odp->ingestOdp();
var_dump($odp->ingest());
?>