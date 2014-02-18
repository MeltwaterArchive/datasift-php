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
	const USER_AGENT      = 'DataSiftPHP/2.1.7';
	const API_BASE_URL    = 'api.datasift.com/';
	const STREAM_BASE_URL = 'stream.datasift.com/';

	/**
	 * @var string The DataSift username.
	 */
	protected $_username = '';

	/**
	 * @var string The DataSift API Key.
	 */
	protected $_api_key = '';

	/**
	 * @var boolean Set to true to enable SSL.
	 */
	protected $_use_ssl = true;

	/**
	 * @var int Stores the X-RateLimit-Limit value from the last API call.
	 */
	protected $_rate_limit = -1;

	/**
	 * @var int Stores the X-RateLimit-Remaining value from the last API call.
	 */
	protected $_rate_limit_remaining = -1;

	/**
	 * @var string The class to use as the API client.
	 */
	protected $_api_client = 'DataSift_ApiClient';

	/**
	 * Constructor. A username and API key are required when constructing an
	 * instance of this class.
	 *
	 * @param string $username The user's username.
	 * @param string $api_key  The user's API key.
	 * @param bool $use_ssl  Set to true to enable SSL.
	 *
	 * @throws DataSift_Exception_InvalidData
	 */
	public function __construct($username, $api_key, $use_ssl = true)
	{
		if (strlen(trim($username)) == 0) {
			throw new DataSift_Exception_InvalidData('Please supply valid credentials when creating a DataSift_User object.');
		}

		if (strlen(trim($api_key)) == 0) {
			throw new DataSift_Exception_InvalidData('Please supply valid credentials when creating a DataSift_User object.');
		}

		$this->_username = $username;
		$this->_api_key  = $api_key;
		$this->_use_ssl  = $use_ssl;
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
	 * Set whether stream connections should use SSL.
	 *
	 * @param bool $use_ssl Set to true to enable SSL.
	 *
	 * @return void
	 */
	public function enableSSL($use_ssl = true)
	{
		$this->_use_ssl = $use_ssl;
	}

	/**
	 * Returns whether SSL should be used where supported.
	 *
	 * @return bool True if SSL should be used.
	 */
	public function useSSL() {
		return $this->_use_ssl;
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

		$retval = $this->callAPI('usage', array('period' => $period));
		return $retval;
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
	 * Create a historic query based on a stream hash.
	 *
	 * @param string $hash    The stream hash.
	 * @param int    $start   The timestamp from which to start the query.
	 * @param int    $end     The timestamp at which to end the query.
	 * @param array  $sources An array of sources required.
	 * @param string $name    A friendly name for this query.
	 * @param float  $sample    An optional sample rate for this query.
	 *
	 * @return DataSift_Historic
	 * @throws DataSift_Exception_InvalidData
	 */
	public function createHistoric($hash, $start, $end, $sources, $name, $sample = DataSift_Historic::DEFAULT_SAMPLE)
	{
		return new DataSift_Historic($this, $hash, $start, $end, $sources, $name, $sample);
	}

	/**
	 * Get an existing historic from the API.
	 *
	 * @param string $playback_id The historic playback ID.
	 *
	 * @return DataSift_Historic
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_AccessDenied
	 */
	public function getHistoric($playback_id)
	{
		return new DataSift_Historic($this, $playback_id);
	}

	/**
	 * Get a list of Historics queries in your account.
	 *
	 * @param int $page The page number to get.
	 * @param int $per_page The number of items per page.
	 *
	 * @return array Of DataSift_Historic objects.
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_AccessDenied
	 */
	public function listHistorics($page = 1, $per_page = 20)
	{
		return DataSift_Historic::listHistorics($this, $page, $per_page);
	}

	/**
	 * Creates and returns a new Push_Definition object.
	 *
	 * @return DataSift_Push_Definition
	 */
	public function createPushDefinition()
	{
		return new DataSift_Push_Definition($this);
	}

	/**
	 * Returns a DataSift_StreamConsumer-derived object for the given hash,
	 * for the given type.
	 *
	 * @param string $type The consumer type for which to construct a consumer.
	 * @param string $hash The hash to be consumed.
	 * @param DataSift_IStreamConsumerEventHandler $eventHandler The object that will receive events.
	 *
	 * @return DataSift_StreamConsumer The consumer object.
	 * @throws DataSift_Exception_InvalidData
	 * @see DataSift_StreamConsumer
	 */
	public function getConsumer($type = DataSift_StreamConsumer::TYPE_HTTP, $hash, $eventHandler)
	{
		return DataSift_StreamConsumer::factory($this, $type, new DataSift_Definition($this, false, $hash), $eventHandler);
	}

	/**
	 * Returns a DataSift_StreamConsumer-derived object for the given hashes,
	 * for the given type.
	 *
	 * @param string $type The consumer type for which to construct a consumer.
	 * @param string $hashes An array containing hashes and/or Definition objects to be consumed.
	 * @param DataSift_IStreamConsumerEventHandler $eventHandler The object that will receive events.
	 *
	 * @return DataSift_StreamConsumer The consumer object.
	 * @throws DataSift_Exception_InvalidData
	 * @see DataSift_StreamConsumer
	 */
	public function getMultiConsumer($type = DataSift_StreamConsumer::TYPE_HTTP, $hashes, $eventHandler)
	{
		return DataSift_StreamConsumer::factory($this, $type, $hashes, $eventHandler);
	}

  /**
   * Get a single push subscription.
   *
   * @param string $id The ID of the subscription to fetch.
   * @return DataSift_Push_Subscription
   * @throws DataSift_Exception_InvalidData
   * @throws DataSift_Exception_AccessDenied
   * @throws DataSift_Exception_APIError
   */
  public function getPushSubscription($id)
  {
  	return DataSift_Push_Subscription::get($this, $id);
  }

	/**
	 * Get a list of push subscriptions in your account.
	 *
	 * @param int $page The page number to get.
	 * @param int $per_page The number of items per page.
	 * @param String order_by  Which field to sort by.
	 * @param String order_dir In asc[ending] or desc[ending] order.
	 * @param bool $include_finished Set to true when you want to include finished subscription in the results.
	 *
	 * @return array Of DataSift_Push_Subscription objects.
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_AccessDenied
	 */
	public function listPushSubscriptions($page = 1, $per_page = 100, $order_by = DataSift_Push_Subscription::ORDERBY_CREATED_AT, $order_dir = DataSift_Push_Subscription::ORDERDIR_ASC, $include_finished = false)
	{
		return DataSift_Push_Subscription::listSubscriptions($this, $page, $per_page, $order_by, $order_dir, $include_finished);
	}

	/**
	 * Page through recent push subscription log entries, specifying the sort
	 * order.
	 *
	 * @param int    page      Which page to fetch.
	 * @param int    per_page  Based on this page size.
	 * @param String order_by  Which field to sort by.
	 * @param String order_dir In asc[ending] or desc[ending] order.
	 * @return ArrayList<LogEntry>
	 * @throws DataSift_Exception_AccessDenied
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_APIError
	 */
	public function getPushSubscriptionLogs($page = 1, $per_page = 100, $order_by = DataSift_Push_Subscription::ORDERBY_REQUEST_TIME, $order_dir = DataSift_Push_Subscription::ORDERDIR_DESC)
	{
		return DataSift_Push_Subscription::getLogs($this, $page, $per_page, $order_by, $order_dir);
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
			$this,
			$endpoint,
			$params,
			$this->getUserAgent()
		);

		$this->_rate_limit = $res['rate_limit'];
		$this->_rate_limit_remaining = $res['rate_limit_remaining'];

		switch ($res['response_code']) {
				case 200:
				case 202:
				case 204:
					$retval = $res['data'];
					break;
				case 401:
					throw new DataSift_Exception_AccessDenied(
						empty($res['data']['error']) ? 'Authentication failed' : $res['data']['error']
					);
				case 413:
					// Request Too Large
					throw new DataSift_Exception_APIError(
						'The API request contained too much data - try reducing the size of your CSDL'
					);
				case 403:
					if ($this->_rate_limit_remaining == 0) {
						throw new DataSift_Exception_RateLimitExceeded($res['data']['error']);
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
