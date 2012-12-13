<?php
/**
 * DataSift client
 *
 * This software is the intellectual property of MediaSift Ltd., and is covered
 * by retained intellectual property rights, including copyright.
 *
 * @category  DataSift
 * @package   PHP-client
 * @author    Stuart Dallas <stuart@3ft9.com>
 * @copyright 2011 MediaSift Ltd.
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      http://www.mediasift.com
 */

/**
 * This script creates a new Historics query from a file containing CSDL.
 *
 * NB: Most of the error handling (exception catching) has been removed for
 * the sake of simplicity. Nearly everything in this library may throw
 * exceptions, and production code should catch them. See the documentation
 * for full details.
 */

// Include the shared convenience class
require dirname(__FILE__).'/env.php';

// Create the env object. This reads the command line arguments, creates the
// user object, and provides access to both along with helper functions.
$env = new Env();

// Check we have the right number of arguments
if (count($env->args) != 6) {
	usage();
}

// Read the arguments
$csdl_filename = $env->args[0];
$start_date    = $env->args[1];
$end_date      = $env->args[2];
$sources       = explode(',', $env->args[3]);
$name          = $env->args[4];
$sample        = $env->args[5];

// Read the CSDL
if (!file_exists($csdl_filename)) {
	usage('CSDL file does not exist');
}
$csdl = file_get_contents($csdl_filename);
if (!$csdl) {
	usage('Failed to read the CSDL file');
}

// If the start and/or end dates are not numbers, attempt to parse them.
if (!is_numeric($start_date)) {
	$start_date = strtotime($start_date);
	if ($start_date == 0) {
		usage('Failed to parse the start date');
	}
}
if (!is_numeric($end_date)) {
	$end_date = strtotime($end_date);
	if ($end_date == 0) {
		usage('Failed to parse the end date');
	}
}

try {
	// Create the definition
	$def = $env->user->createDefinition($csdl);

	// Create the Historics query.
	$hist = $def->createHistoric($start_date, $end_date, $sources, $name, $sample);

	// Prepare the query (sends it to the DataSift API for validation and creation).
	$hist->prepare();

	// Display the details. Use the start script to start the query.
	$env->displayHistoricDetails($hist);
} catch (Exception $e) {
	echo 'ERR: '.get_class($e).' '.$e->getMessage().PHP_EOL;
}

/**
 * Return usage information.
 * 
 * @param string $message Custom message.
 * @param bool $exit Set to true if you want to exit the script.
 */
function usage($message = '', $exit = true)
{
	if (strlen($message) > 0) {
		echo PHP_EOL.$message.PHP_EOL;
	}
	echo PHP_EOL;
	echo 'Usage: create_from_hash <username> <api_key> \\'.PHP_EOL;
	echo '                <csdl_filename> <start> <end> <sources> <name> <sample>'.PHP_EOL;
	echo PHP_EOL;
	echo 'Where: csdl_filename = a file containing the CSDL the query should run'.PHP_EOL;
	echo '       start   = the start date for the query (unix timestamp or parsable string)'.PHP_EOL;
	echo '       end     = the end date for the query (unix timestamp or parsable string)'.PHP_EOL;
	echo '       sources = comma separated list of data sources (e.g. twitter)'.PHP_EOL;
	echo '       name    = a friendly name for the query'.PHP_EOL;
	echo '       sample  = the sample rate'.PHP_EOL;
	echo PHP_EOL;
	echo 'Example'.PHP_EOL;
	echo '       create_from_csdl <csdl_filename> \"2012-01-01 00:00:00\" \"2012-01-01 23:59:59\" \\'.PHP_EOL;
	echo '                      twitter \"historics query 123\" 100'.PHP_EOL;
	echo PHP_EOL;
	if ($exit) {
		exit;
	}
}
