<?php
// Include the DataSift library
require dirname(__FILE__) . '/../lib/datasift.php';

// Include the configuration - put your username and API key in this file
require dirname(__FILE__) . '/../config.php';

// Authenticate
echo "Creating user...\n";
$user = new DataSift_User(USERNAME, API_KEY);

$preview = new DataSift_HistoricPreview($user, array(
	'start' => time() - 14400, //4hrs ago
	'end' => time() - 7200, //optional and can be omitted
	'hash' => '03e12600b2c417347c5028f34ecda005',
	'parameters' => array('interaction.author.link', 'targetVol', 'hour;twitter.user.lang'),
));

echo "Creating historics preview\n";
$preview->create();
echo 'Created preview with ID => ' . $preview->getId() . "\n";

$id = $preview->getId();

while ($preview->getData() == NULL) {
	echo "Waiting before checking back for data\n";
	sleep(30);
	$preview = DataSift_HistoricPreview::get($user, $preview->getId());
}

echo "Got preview data\n";
print_r($preview->getData());
