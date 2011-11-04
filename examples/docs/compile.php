<?php

/**
 * This example demonstrates
 * + creating a CSDL statement
 * + making an API request to Datasift
 * +displaying the data returned
 */
//Step 1 Include our authentication details
include 'config.php';

//Step 2 - Include the DataSift library - This is the only file "required"*
require '../../lib/datasift.php';

//Step 3 - We need to create a "User" object for authentication
$user = new DataSift_User(USERNAME, API_KEY);

/**
 * Step 4 - This where we define our CSDL statement for compilation
 * The 'words' array is a set of key words we are interesrted in tracking via twitter
 * Notice the 'interaction.type=="twitter"' ? This allows us to only get data posted on twitter
 * The use of implode simply concats each word in the words array with a prefix which results in a string like:
 * interaction.type == "twitter" and (interaction.content contains "music" or interaction.content contains "mtv"
 * or interaction.content contains "itv" or interaction.content contains "skyb2b" or interaction.content contains
 * "news" or interaction.content contains "csi" or interaction.content contains "criminal minds")
 */
$words = array('music', 'mtv', 'itv', 'skyb2b', 'news', 'csi', 'criminal minds');
// Create the definition
$csdl = 'interaction.type == "twitter" and (interaction.content contains "' . implode('" or interaction.content contains "', $words) . '")';

//Step 5 - Creat a definition using the user object and the generated CSDL
$definition = new DataSift_Definition($user, $csdl);

//some vars to use later
$hash = null;
$created = null;
$dpu = null;

//Step 6 - explicitly compile the definition
try {
	$definition->compile();
//Step 7 - If everything is okay then we can now get the hash,created_at and DPU values that are returned
	$hash = $definition->getHash();
	$created = $definition->getCreatedAt();
	$dpu = $definition->getTotalDPU();

	print 'Stream hash : ' . $hash . " \n";
	print 'Created at : ' . $created . " \n";
	print 'Total DPU : ' . $dpu . " \n";
}
catch (Exception $e) {
	//If there is an exception then a few things could have gone wrong so the message included would help
	echo 'Caught exception: ', $e->getMessage(), "\n";
}