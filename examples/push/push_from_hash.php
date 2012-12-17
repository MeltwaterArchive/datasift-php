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
 * This script lists push subscriptions in your account.
 *
 * NB: Most of the error handling (exception catching) has been removed for
 * the sake of simplicity. Nearly everything in this library may throw
 * exceptions, and production code should catch them. See the documentation
 * for full details.
 */

// Include the shared convenience class
require dirname(__FILE__).'/env.php';

// Create the env object. This reads the command line arguments, creates the
// user object, and provides access to both along with helper functions
$env = new Env();

// Check we have enough arguments
if (count($env->args) < 4) {
	usage();
}

// Fixed args
$output_type = $env->args[0];
$hash_type   = $env->args[1];
$hash        = $env->args[2];
$name        = $env->args[3];

// The rest of the args will be output parameters, and we'll use them later

try {
	$push_definition = $env->user->createPushDefinition();
	$push_definition->setOutputType($output_type);

	// Now add the output_type-specific args from the command line
	for ($i = 4; $i < count($env->args); $i++) {
		$bits = explode('=', $env->args[$i], 2);
		if (count($bits) != 2) {
			usage('Invalid output_param: '.$env->args[$i]);
		}
		$push_definition->setOutputParam($bits[0], $bits[1]);
	}

	// Subscribe the definition to the hash
	switch ($hash_type) {
		case 'stream':
			$push_sub = $push_definition->subscribeStreamHash($hash, $name);
			break;
		case 'historic':
			$push_sub = $push_definition->subscribeHistoricPlaybackId($hash, $name);
			break;
		default:
			usage('Invalid hash_type: '.$hash_type);
			exit;
	}

	// Display the details of the new subscription
	$env->displaySubscriptionDetails($push_sub);
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
	echo 'Usage: push_from_hash \\'.PHP_EOL;
	echo '            <username> <api_key> <output_type> <hash_type> <hash> <name> ...'.PHP_EOL;
	echo PHP_EOL;
	echo 'Where: output_type = http (currently only http is supported)'.PHP_EOL;
	echo '       hash_type   = stream | historic'.PHP_EOL;
	echo '       hash        = the hash'.PHP_EOL;
	echo '       name        = a friendly name for the subscription'.PHP_EOL;
	echo '       key=val     = output_type-specific arguments'.PHP_EOL;
	echo PHP_EOL;
	echo 'Example'.PHP_EOL;
	echo '       push_from_hash http stream <hash> \"Push Name\" delivery_frequency=10 \\'.PHP_EOL;
	echo '                      url=http://www.example.com/push_endpoint auth.type=none'.PHP_EOL;
	echo PHP_EOL;
	if ($exit) {
		exit;
	}
}
