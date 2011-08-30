<?php
	/**
	 * This example will get the usage statistics for the configured user and
	 * display them in a pretty table. If a stream hash is passed on the
	 * the command line, statistics for that stream are displayed.
	 *
	 * NB: Most of the error handling (exception catching) has been removed for
	 * the sake of simplicity. Nearly everything in this library may throw
	 * exceptions, and production code should catch them. See the documentation
	 * for full details.
	 */

	// Include the DataSift library
	require dirname(__FILE__).'/../lib/datasift.php';

	// Include the configuration - put your username and API key in this file
	require dirname(__FILE__).'/../config.php';

	// Drop the script name
	array_shift($_SERVER['argv']);

	// Have we been given a hash?
	$hash = false;
	if (count($_SERVER['argv']) > 0) {
		$hash = array_shift($_SERVER['argv']);
	}

	// Authenticate
	echo "Creating user...\n";
	$user = new DataSift_User(USERNAME, API_KEY);

	// Get the usage statistics
	echo "Getting usage...\n";
	$usage = $user->getUsage(false, false, $hash);

	// Format the usage stats for output in a table
	$table = array();
	$maxlength = array(
		'type' => strlen($hash === false ? 'Stream' : 'Type'),
		'processed' => max(strlen('Processed'), strlen(number_format($usage['processed']))),
		'delivered' => max(strlen('Delivered'), strlen(number_format($usage['delivered']))),
	);

	foreach ($usage[$hash === false ? 'streams' : 'types'] as $type => $u) {
		$maxlength['type'] = max($maxlength['type'], strlen($type));
		$maxlength['processed'] = max($maxlength['processed'], strlen(number_format($u['processed'])));
		$maxlength['delivered'] = max($maxlength['delivered'], strlen(number_format($u['delivered'])));

		$table[] = array(
			'type'      => $type,
			'processed' => number_format($u['processed']),
			'delivered' => number_format($u['delivered']),
		);
	}

	// Output the table
	echo "\n";
	echo '/-'.str_repeat('-', $maxlength['type']).'---';
	echo str_repeat('-', $maxlength['processed']).'---';
	echo str_repeat('-', $maxlength['delivered'])."-\\\n";

	echo '| '.str_pad($hash === false ? 'Stream' : 'Type', $maxlength['type']).' | ';
	echo str_pad('Processed', $maxlength['processed']).' | ';
	echo str_pad('Delivered', $maxlength['delivered'])." |\n";

	echo '|-'.str_repeat('-', $maxlength['type']).'-+-';
	echo str_repeat('-', $maxlength['processed']).'-+-';
	echo str_repeat('-', $maxlength['delivered'])."-|\n";

	foreach ($table as $row) {
		echo '| '.str_pad($row['type'], $maxlength['type']).' | ';
		echo str_pad($row['processed'], $maxlength['processed'], ' ', STR_PAD_LEFT).' | ';
		echo str_pad($row['delivered'], $maxlength['delivered'], ' ', STR_PAD_LEFT)." |\n";
	}

	echo '|-'.str_repeat('-', $maxlength['type']).'---';
	echo str_repeat('-', $maxlength['processed']).'---';
	echo str_repeat('-', $maxlength['delivered'])."-|\n";

	echo '| '.str_pad('Totals', $maxlength['type'], ' ', STR_PAD_LEFT).' | ';
	echo str_pad(number_format($usage['processed']), $maxlength['processed'], ' ', STR_PAD_LEFT).' | ';
	echo str_pad(number_format($usage['delivered']), $maxlength['delivered'], ' ', STR_PAD_LEFT)." |\n";

	echo '\\-'.str_repeat('-', $maxlength['type']).'---';
	echo str_repeat('-', $maxlength['processed']).'---';
	echo str_repeat('-', $maxlength['delivered'])."-/\n\n";
