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
	const USER_AGENT      = 'DataSiftPHP/1.0.0';
	const API_BASE_URL    = 'api.datasift.com/';
	const STREAM_BASE_URL = 'stream.datasift.com/';

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
	 * Returns the recordings stored in this user's DataSift account.
	 *
	 * @param int $page The page to return.
	 * @param int $count The number of items per page.
	 *
	 * @return array An array of DataSift_Recording objects.
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_APIError
	 */
	public function getRecordings($page = 1, $count = 20)
	{
		if (intval($page) != $page or $page < 1) {
			throw new DataSift_Exception_InvalidData('The page parameter must be an integer > 0');
		}
		if (intval($count) != $count or $count < 1) {
			throw new DataSift_Exception_InvalidData('The count parameter must be an integer > 0');
		}

		$recordings = $this->callAPI('recording', array('page' => intval($page), 'count' => intval($count)));

		$retval = array();

		foreach ($recordings['recordings'] as $recording)
		{
			$retval[] = new DataSift_Recording($this, $recording);
		}

		return $retval;
	}

	/**
	 * Returns a single recording stored in this user's DataSift account.
	 *
	 * @param string $id The ID of the recording to return.
	 *
	 * @return DataSift_Recording A DataSift_Recording object representing the requested recording.
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_APIError
	 */
	public function getRecording($id)
	{
		if (!is_string($id)) {
			throw new DataSift_Exception_InvalidData('The ID parameter must be a string');
		}

		$recording = $this->callAPI('recording', array('id' => $id));

		return new DataSift_Recording($this, $recording);
	}

	/**
	 * Schedule a new recording.
	 *
	 * @param string $hash The hash of the stream to record.
	 * @param string $name An optional name for the recording.
	 * @param string $start An optional unix timestamp defining when the recording should start.
	 * @param string $end An optional unix timestamp defining when the recording should end.
	 *
	 * @return DataSift_Recording A DataSift_Recording object representing the newly created recording.
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_APIError
	 */
	public function scheduleRecording($hash, $name = '', $start = false, $end = false)
	{
		// Validate the parameters
		if (!is_string($hash)) {
			throw new DataSift_Exception_InvalidData('The hash parameter must be a string');
		}
		if (!is_string($name)) {
			throw new DataSift_Exception_InvalidData('The name parameter must be a string');
		}
		if ($start !== false and !is_integer($start)) {
			throw new DataSift_Exception_InvalidData('The start parameter must be an integer timestamp');
		}
		if ($end !== false and !is_integer($end)) {
			throw new DataSift_Exception_InvalidData('The end parameter must be an integer timestamp');
		}

		// Build the parameters
		$params = array('hash' => $hash);
		if (strlen($name) > 0) {
			$params['name'] = $name;
		}
		if ($start !== false) {
			$params['start'] = $start;
		}
		if ($end !== false) {
			$params['end'] = $end;
		}

		$recording = $this->callAPI('recording/schedule', $params);

		return new DataSift_Recording($this, $recording);
	}

	/**
	 * Returns the exports stored in this user's DataSift account.
	 *
	 * @param int $page The page to return.
	 * @param int $count The number of items per page.
	 *
	 * @return array An array of DataSift_RecordingExport objects.
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_APIError
	 */
	public function getExports($page = 1, $count = 20)
	{
		if (intval($page) != $page or $page < 1) {
			throw new DataSift_Exception_InvalidData('The page parameter must be an integer > 0');
		}
		if (intval($count) != $count or $count < 1) {
			throw new DataSift_Exception_InvalidData('The count parameter must be an integer > 0');
		}

		$exports = $this->callAPI('recording/export', array('page' => intval($page), 'count' => intval($count)));

		$retval = array();

		foreach ($exports['exports'] as $export)
		{
			$retval[] = new DataSift_RecordingExport($this, $export);
		}

	/**
	 * Returns the usage data for this user.
	 *
	 * @param string $period Either 'hour' or 'day'.
	 *
	 * @return array The usage data from the API.
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_APIError
	 */
	public function getUsage($period = 'hour')
	{
		$retval = false;

		if (!in_array($period, array('hour', 'day'))) {
			throw new DataSift_Exception_InvalidData('The period parameter must be either "hour" or "day"!');
		}

		$retval = $this->callAPI('usage', array('period' => $period));
		return $retval;
	}

	/**
	 * Returns a single export stored in this user's DataSift account.
	 *
	 * @param string $id The ID of the recording to return.
	 *
	 * @return DataSift_Recording A DataSift_Recording object representing the requested recording.
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_APIError
	 */
	public function getExport($id)
	{
		if (!is_string($id)) {
			throw new DataSift_Exception_InvalidData('The ID parameter must be a string');
		}

		$export = $this->callAPI('recording/export', array('id' => $id));

		return new DataSift_Recording($this, $export);
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
	 * Returns a DataSift_StreamConsumer-derived object for the given hash,
	 * for the given type.
	 *
	 * @param string $type The consumer type for which to construct a consumer.
	 * @param string $hash The hash to be consumed.
	 *
	 * @return DataSift_StreamConsumer The consumer object.
	 * @throws DataSift_Exception_InvalidData
	 * @see DataSift_StreamConsumer
	 */
	public function getConsumer($type = DataSift_StreamConsumer::TYPE_HTTP, $hash, $onInteraction = false, $onStopped = false)
	{
		return DataSift_StreamConsumer::factory($this, $type, new DataSift_Definition($this, false, $hash), $onInteraction, $onStopped);
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
}
