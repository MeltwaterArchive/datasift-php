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

if (function_exists('date_default_timezone_set')) {
	date_default_timezone_set('UTC');
}

// Include the DataSift library
require dirname(__FILE__).'/../../lib/datasift.php';

/**
 * This class is used by the push examples to remove the noise of dealing
 * with command line arguments.
 */
class Env
{
	/**
	 * @var DataSift_User The user object
	 */
	public $user = null;

	/**
	 * @var array Argsument array
	 */
	public $args = array();

  /**
   * Env constructor.
   *
   * @param array $args Arguments
   */
	public function __construct($args = false)
	{
		// If no args were passed, use the command line args
		if ($args === false) {
			$args = $_SERVER['argv'];
			// Drop the script name
			array_shift($args);
		}

		// Make sure we have credentials on the command line
		if (count($args) < 2) {
			die('Please specify your DataSift username and API key as the first two command line arguments!'.PHP_EOL);
		}
		
		try {
			$username = array_shift($args);
			$api_key = array_shift($args);
			$this->user = new DataSift_User($username, $api_key);
		} catch (Exception $e) {
			die('Failed to create the DataSift_User object - check your username and API key!'.PHP_EOL);
		}
		
		$this->args = $args;
	}
	
  /**
   * Displays details of a Historics query.
   *
   * @param DataSift_Push_Subscription $sub A DataSift Push Subscription object
   */
	public function displaySubscriptionDetails($sub)
	{
		echo 'ID:            '.$sub->getId().PHP_EOL;
		echo 'Name:          '.$sub->getName().PHP_EOL;
		echo 'Status:        '.$sub->getStatus().PHP_EOL;
		echo 'Created at:    '.(is_null($sub->getCreatedAt()) ? 'null' : date('r', $sub->getCreatedAt())).PHP_EOL;
		echo 'Last request:  '.(is_null($sub->getLastRequest()) ? 'null' : date('r', $sub->getLastRequest())).PHP_EOL;
		echo 'Last success:  '.(is_null($sub->getLastSuccess()) ? 'null' : date('r', $sub->getLastSuccess())).PHP_EOL;
		echo 'Output Type:   '.$sub->getOutputType().PHP_EOL;

		echo 'Output Params:'.PHP_EOL;
		foreach ($sub->getOutputParams() as $key => $val) {
			echo '  '.$key.' = '.$val.PHP_EOL;
		}
	}
}
