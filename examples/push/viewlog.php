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
 * This script displays push subscription logs from your account.
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

try {
	switch (count($env->args)) {
		case 0:
			$log = $env->user->getPushSubscriptionLogs();
			if (count($log['log_entries']) == 0) {
				echo 'No log entries found.'.PHP_EOL;
			} else {
				foreach ($log['log_entries'] as $entry) {
					echo date('Y-m-d H:i:s', $entry->getRequestTime()).' ['.$entry->getSubscriptionId().'] '.($entry->getSuccess() ? 'Success' : '').' '.$sub->getMessage().PHP_EOL;
				}
			}
			break;

		case 1:
			// Get the subscription ID
			$subscription_id = array_shift($env->args);
			// Get the subscription
			$sub = $env->user->getPushSubscription($subscription_id);
			// Get the log
			$log = $sub->getLog();
			// Display the log
			if (count($log['log_entries']) == 0) {
				echo 'No log entries found.'.PHP_EOL;
			} else {
				foreach ($log['log_entries'] as $entry) {
					echo date('Y-m-d H:i:s', $entry->getRequestTime()).' '.($entry->getSuccess() ? 'Success' : '').' '.$sub->getMessage().PHP_EOL;
				}
			}
			break;

		default:
			die('Only one subscription ID can be specified!'.PHP_EOL);
	}
} catch (Exception $e) {
	echo 'ERR: '.get_class($e).' '.$e->getMessage().PHP_EOL;
}
