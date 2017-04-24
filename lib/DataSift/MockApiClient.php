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

class DataSift_MockApiClient extends DataSift_ApiClient
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
	public static function setResponse($r, $response_code=200)
	{	
		if (is_string($r)) {
			$res = self::parseHTTPResponse($r);

			$info = array('http_code' => $response_code);

	        $r = array(
	            'response_code'        => $info['http_code'],
	            'data'                 => (strlen($res['body']) == 0 ? array() : self::decodeBody($res)),
	            'rate_limit'           => (isset($res['headers']['x-ratelimit-limit']) ? $res['headers']['x-ratelimit-limit'] : -1),
	            'rate_limit_remaining' => (isset($res['headers']['x-ratelimit-remaining']) ? $res['headers']['x-ratelimit-remaining'] : -1),
	        );

		}
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
	static public function call(
        DataSift_User $user, 
        $endPoint,
        $method,
        $params = array(),
        $headers = array(),
        $userAgent = DataSift_User::USER_AGENT,
        $qs = array(),
        $ingest = false
    )
	{
		if (self::$_response === false) {
			throw new Exception('Expected response not set in mock object');
		}
		return self::$_response;
	}

    static public function get(DataSift_User $user, $endpoint, $params = array(), $userAgent = DataSift_User::USER_AGENT, $successCode) 
    {
        return self::call($user, $endpoint, $params, $userAgent);
    }
   
    static public function post(DataSift_User $user, $endpoint, $params, $userAgent = DataSift_User::USER_AGENT, $successCode)
    {
        return self::call($user, $endpoint, $params, $userAgent);
    }

    static public function put(DataSift_User $user, $endpoint, $params, $userAgent = DataSift_User::USER_AGENT, $successCode)
    {
        return self::call($user, $endpoint, $params, $userAgent);
    }

    static public function delete(DataSift_User $user, $endpoint, $params = array(), $userAgent = DataSift_User::USER_AGENT, $successCode) 
    {
        return self::call($user, $endpoint, $params, $userAgent);
    }
}
