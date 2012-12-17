<?php
/**
 * DataSift client
 *
 * The DataSift_MockApiClient class is used in place of DataSift_ApiClient
 * in offline unit tests.
 *
 * This software is the intellectual property of MediaSift Ltd., and is covered
 * by retained intellectual property rights, including copyright.
 * Distribution of this software is strictly forbidden under the terms of this license.
 *
 * @category  DataSift
 * @package   PHP-client
 * @author    Stuart Dallas <stuart@3ft9.com>
 * @copyright 2011 MediaSift Ltd.
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      http://www.mediasift.com
 */

/**
 * The DataSift_MockApiClient class is used in place of DataSift_ApiClient
 * in offline unit tests.
 *
 * @category  DataSift
 * @package   PHP-client
 * @author    Stuart Dallas <stuart@3ft9.com>
 * @copyright 2011 MediaSift Ltd.
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      http://www.mediasift.com
 */
class DataSift_MockApiClient
{
  /**
   * @var string $_response the API response
   */
	static private $_response = false;

	/**
	 * Set the response object
	 *
	 * @param object $r Response
	 *
	 * @return void
	 */
	public static function setResponse($r)
	{
		self::$_response = $r;
	}
	
	/**
	 * Set the response object
	 *
	 * @param string $username   Username
	 * @param string $api_key    API key
	 * @param string $endpoint   URL
	 * @param array  $params     URL parameters
	 * @param string $user_agent User Agent string
	 *
	 * @return void
	 */
	public static function call($username, $api_key, $endpoint, $params = array(), $user_agent = 'DataSiftPHP/0.0')
	{
		if (self::$_response === false) {
			throw new Exception('Expected response not set in mock object');
		}
		return self::$_response;
	}
}
