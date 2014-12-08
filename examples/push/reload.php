<?php
/**
 * DataSift client
 *
 * This software is the intellectual property of MediaSift Ltd., and is covered
 * by retained intellectual property rights, including copyright.
 *
 * @category  DataSift
 * @package   PHP-client
 * @author    Paul Mozo <paul.mozo@datasift.com>
 * @copyright 2011 MediaSift Ltd.
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      http://www.mediasift.com
 */

/**
 * This script loads all active subscriptions, reloads them and outputs their status.
 *
 */
require dirname(__FILE__) . '/../../lib/datasift.php';
require dirname(__FILE__) . '/../../config.php';

$user = new DataSift_User(USERNAME, API_KEY);

$subs = DataSift_Push_Subscription::listSubscriptions($user);

if ($subs['count'] > 0) 
{
	foreach ($subs['subscriptions'] as $sub) 
	{
		$sub->reload();
		echo $sub->getId()." Status: ".$sub->getStatus()."\n";
	}
}
else
{
	echo "No active subscriptions";
}
