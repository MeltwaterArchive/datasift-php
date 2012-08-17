<?php
	/**
	 * This simple script wraps up calling the push examples.
	 *
	 * Usage: push.php <username> <api_key> <command> <arg1> <arg2> ... <argn>
	 *
	 * Note all var names are prefixed with ____ to avoid stamping on anything.
	 */

	// Drop the script name
	array_shift($_SERVER['argv']);

	// Make sure we have enough
	if (count($_SERVER['argv']) < 3) {
		die('Usage: '.basename(__FILE__).' <username> <api_key> <command> <arg1> <arg2> ... <argn>'.PHP_EOL);
	}

	// Get the user details
	$____username = array_shift($_SERVER['argv']);
	$____api_key  = array_shift($_SERVER['argv']);

	// Get the command
	$____command = array_shift($_SERVER['argv']);

	// Push the user details back on to the args, in reverse order since we're
	// pushing on the the front of the array
	array_unshift($_SERVER['argv'], $____api_key);
	array_unshift($_SERVER['argv'], $____username);

	// Does the command script exist?
	$____command_filename = dirname(__FILE__).'/push/'.$____command.'.php';
	if (!file_exists($____command_filename)) {
		die('Command "'.$____command.'" does not exist!'.PHP_EOL);
	}

	// Add the script name to the start of the args
	array_unshift($_SERVER['argv'], $____command_filename);

	// Include the script
	require($____command_filename);
