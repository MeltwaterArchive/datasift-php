<?php
// Include the DataSift library
require dirname(__FILE__) . '/../lib/datasift.php';

// Include the configuration - put your username and API key in this file
require dirname(__FILE__) . '/../config.php';

// Authenticate
echo "Creating user...\n";
$user = new DataSift_User(USERNAME, API_KEY);

//create params
$params = array(
	'likes' => true,
	'page_likes' => true,
	'comments' => true,
	'posts_by_others' => true,
);

//can create using an stdClass if preferred
$theguardian = new stdClass();
$theguardian->parameters = new stdClass();
$theguardian->parameters->url = 'http://www.facebook.com/theguardian';
$theguardian->parameters->title = 'Some news page';
$theguardian->parameters->id = 'theguardian';

//or using an array
$ladyGaga = new stdClass();
$ladyGaga->parameters = array(
	'url' => 'http://www.facebook.com/ladygaga',
	'title' => 'Lady Gaga',
	'id' => 'ladygaga'
);


$resources = array($theguardian, $ladyGaga);

$facebookAuth1 = new stdClass();
$facebookAuth1->parameters = new stdClass();
$facebookAuth1->parameters->value = 'facebook_token';

//one or more facebook OAuth tokens can be used to manage the resources
$auth = array(
	$facebookAuth1,
);

$source = new DataSift_Source($user, array(
	'name' => 'My PHP managed source',
	'source_type' => 'facebook_page',
	'parameters' => $params,
	'auth' => $auth,
	'resources' => $resources
));

//create the managed source - note the same method is used to update an existing
//managed source if an ID is set on the object
$source->save();
//after saving the source will have an ID, created at time etc...
echo 'Created managed source ==> ' . $source->getId();

//Add an extra resource
$nintendo = new stdClass();
$nintendo->parameters = array(
	'url' => 'http://www.facebook.com/nintendo',
	'title' => 'Nintendo',
	'id' => 'nintendo'
);
$source->addResource(array($nintendo));

$resources = $source->getResources();

//remove the first resource
$source->removeResource(array($resources[0]['resource_id']));

//Check for log messages
$logs = $source->getLogs();

