<?php
/**
 * DataSift client
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
 * The DataSift_User class represents a user of the API. Applications should
 * start their API interactions by creating an instance of this class. Once
 * initialised it provides factory methods for all of the functionality in
 * the API.
 *
 * @category DataSift
 * @package  PHP-client
 * @author   Stuart Dallas <stuart@3ft9.com>
 * @license  http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link     http://www.mediasift.com
 */
class DataSift_User
{
	const USER_AGENT      = 'DataSiftPHP/0.96';
	const API_BASE_URL    = 'api.datasift.net/';
	const STREAM_BASE_URL = 'stream.datasift.net/';

	/**
	 * @var string
	 */
	protected $_username = '';

	/**
	 * @var string
	 */
	protected $_api_key = '';

	/**
	 * Stores the X-RateLimit-Limit value from the last API call.
	 * 
	 * @var int
	 */
	protected $_rate_limit = -1;

	/**
	 * Stores the X-RateLimit-Remaining value from the last API call.
	 * 
	 * @var int
	 */
	protected $_rate_limit_remaining = -1;

	/**
	 * The class to use as the API client
	 *.
	 * @var string
	 */
	protected $_api_client = 'DataSift_ApiClient';

	/**
	 * Constructor. A username and API key are required when constructing an
	 * instance of this class.
	 *
	 * @param string $username The user's username.
	 * @param string $api_key  The user's API key.
	 * 
	 * @throws DataSift_Exception_InvalidData
	 */
	public function __construct($username, $api_key)
	{
		if (strlen(trim($username)) == 0) {
			throw new DataSift_Exception_InvalidData('Please supply valid credentials when creating a DataSift_User object.');
		}

		if (strlen(trim($api_key)) == 0) {
			throw new DataSift_Exception_InvalidData('Please supply valid credentials when creating a DataSift_User object.');
		}

		$this->_username = $username;
		$this->_api_key  = $api_key;
	}
	
	/**
	 * Set the class to use when calling the API
	 *
	 * @param string $api_client The class to use.
	 *
	 * @return void
	 * @throws DataSift_Exception_InvalidData
	 */
	public function setApiClient($api_client)
	{
		if (!class_exists($api_client) or !method_exists($api_client, 'call')) {
			throw new DataSift_Exception_InvalidData('Class "'.$api_client.'" does not exist');
		}
		$this->_api_client = $api_client;
	}

	/**
	 * Returns the username.
	 *
	 * @return string The username.
	 */
	public function getUsername()
	{
		return $this->_username;
	}

	/**
	 * Returns the API key.
	 *
	 * @return string The API key.
	 */
	public function getAPIKey()
	{
		return $this->_api_key;
	}

	/**
	 * Returns the rate limit returned by the last API call.
	 *
	 * @return int The rate limit.
	 */
	public function getRateLimit()
	{
		return $this->_rate_limit;
	}

	/**
	 * Returns the rate limit remaining returned by the last API call.
	 *
	 * @return int The rate limit remaining.
	 */
	public function getRateLimitRemaining()
	{
		return $this->_rate_limit_remaining;
	}

	/**
	 * Creates and returns an empty Definition object.
	 *
	 * @param string $definition Optional definition with which to prime the object.
	 *
	 * @return DataSift_Definition A definition object tied to this user.
	 */
	public function createDefinition($definition = '')
	{
		return new DataSift_Definition($this, $definition);
	}

	/**
	 * Returns the user agent this library should use for all API calls.
	 *
	 * @return string
	 */
	public function getUserAgent()
	{
		return self::USER_AGENT;
	}

	/**
	 * Make a call to a DataSift API endpoint.
	 *
	 * @param string $endpoint The endpoint of the API call.
	 * @param array  $params   The parameters to be passed along with the request.
	 *
	 * @return array The response from the server.
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_RateLimitExceeded
	 */
	public function callAPI($endpoint, $params = array())
	{
		$res = call_user_func(
			array($this->_api_client, 'call'), 
			$this->_username, 
			$this->_api_key, 
			$endpoint, 
			$params, 
			$this->getUserAgent()
		);

		$this->_rate_limit = $res['rate_limit'];
		$this->_rate_limit_remaining = $res['rate_limit_remaining'];
		
		switch ($res['response_code']) {
				case 200:
					$retval = $res['data'];
					break;
				case 401:
					throw new DataSift_Exception_AccessDenied(
						empty($res['data']['error']) ? 'Authentication failed' : $res['data']['error']
					);
				case 403:
					if ($this->_rate_limit_remaining == 0) {
						throw new DataSift_Exception_RateLimitExceeded($res['data']['comment']);
					}
					// Deliberate fall-through
				default:
					throw new DataSift_Exception_APIError(
						empty($res['data']['error']) ? 'Unknown error' : $res['data']['error'], $res['response_code']
					);
		}
		
		return $retval;
	}

	/**
	 * Parse an HTTP response. Separates the headers from the body and puts
	 * the headers into an associative array.
	 *
	 * @param string $str The HTTP response to be parsed.
	 *
	 * @return array An array containing headers => array(header => value), and body.
	 */
	private function parseHTTPResponse($str)
	{
		//var_dump($str);
		$retval = array('headers' => array(), 'body' => '');
		$lastfield = false;
		$fields = explode("\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $str));
		foreach ($fields as $field) {
			if (strlen(trim($field)) == 0) {
				$lastfield = ':body';
			} elseif ($lastfield == ':body') {
				$retval['body'] .= $field."\n";
			} else {
				if (($field[0] == ' ' or $field[0] == "\t") and $lastfield !== false) {
					$retval['headers'][$lastfield] .= ' '.$field;
				} elseif (preg_match('/([^:]+): (.+)/m', $field, $match)) {
					$match[1] = strtolower(preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1]))));
					if (isset($retval['headers'][$match[1]])) {
						if (is_array($retval['headers'][$match[1]])) {
							$retval['headers'][$match[1]][] = $match[2];
						} else {
							$retval['headers'][$match[1]] = array($retval['headers'][$match[1]], $match[2]);
						}
					} else {
						$retval['headers'][$match[1]] = trim($match[2]);
					}
				}
			}
		}
		return $retval;
	}
}
