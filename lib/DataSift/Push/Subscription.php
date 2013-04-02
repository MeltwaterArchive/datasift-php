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
 * The DataSift_Push_Subscription class represents a push subscription.
 *
 * @category DataSift
 * @package  PHP-client
 * @author   Stuart Dallas <stuart@3ft9.com>
 * @license  http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link     http://www.mediasift.com
 */
class DataSift_Push_Subscription extends DataSift_Push_Definition {
	/**
	 * Hash type constants.
	 */
	const HASH_TYPE_STREAM   = 'stream';
	const HASH_TYPE_HISTORIC = 'historic';
	
	/**
	 * Push subscription status constants.
	 */
	const STATUS_ACTIVE    = 'active';
	const STATUS_PAUSED    = 'paused';
	const STATUS_STOPPED   = 'stopped';
	const STATUS_FINISHING = 'finishing';
	const STATUS_FINISHED  = 'finished';
	const STATUS_FAILED    = 'failed';
	const STATUS_DELETED   = 'deleted';
	
	/**
	 * Order by constants.
	 */
	const ORDERBY_ID           = 'id';
	const ORDERBY_CREATED_AT   = 'created_at';
	const ORDERBY_REQUEST_TIME = 'request_time';
	
	/**
	 * Order direction constants.
	 */
	const ORDERDIR_ASC  = 'asc';
	const ORDERDIR_DESC = 'desc';
	
	/**
	 * Get a push subscription by ID.
	 * 
	 * @param DataSift_User $user The user who owns the subscription.
	 * @param string        $id   The subscription ID.
	 *
	 * @return DataSift_Push_Subscription
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_AccessDenied
	 * @throws DataSift_Exception_InvalidData
	 */
	static public function get($user, $id)
	{
		$params = array('id' => $id);
		return new self($user, $user->callAPI('push/get', $params));
	}

	/**
	 * Get a page of push subscriptions in the given user's account, where
	 * each page contains up to per_page items. Results will be ordered
	 * according to the supplied ordering parameters.
	 * 
	 * @param DataSift_User $user             The user.
	 * @param int           $page             The page number to fetch.
	 * @param int           $per_page         The number of items per page.
	 * @param string        $order_by         The field on which to order the results.
	 * @param string        $order_dir        The direction of the ordering.
	 * @param boolean       $include_finished True to include subscriptions against
	 *                                        finished historic queries.
	 * @param string        $hash_type        Stream hash or Historics playback id.
	 * @param string        $hash             The stream hash or historics subscription id string.
	 *
	 * @return array Of DataSift_Push_Subscription objects.
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_AccessDenied
	 */
	static public function listSubscriptions($user, $page = 1, $per_page = 20, $order_by = self::ORDERBY_CREATED_AT, $order_dir = self::ORDERDIR_ASC, $include_finished = false, $hash_type = false, $hash = false)
	{
		if ($page < 1) {
			throw new DataSift_Exception_InvalidData("The specified page number is invalid");
		}
		
		if ($per_page < 1) {
			throw new DataSift_Exception_InvalidData("The specified per_page value is invalid");
		}
		
		$params = array(
			'page' => $page,
			'per_page' => $per_page,
			'order_by' => $order_by,
			'order_dir' => $order_dir,
		);

		if ($hash_type !== false && $hash !== false) {
			$params[$hash_type] = $hash;
		}

		if ($include_finished) {
			$params['include_finished'] = '1';
		}

		$res = $user->callAPI('push/get', $params);

		$retval = array('count' => $res['count'], 'subscriptions' => array());
		foreach ($res['subscriptions'] as $sub) {
			$retval['subscriptions'][] = new self($user,$sub);
		}
		
		return $retval;
	}
	
	/**
	 * Get a page of push subscriptions to the given stream hash, where
	 * each page contains up to per_page items. Results will be ordered
	 * according to the supplied ordering parameters.
	 * 
	 * @param DataSift_User $user             The user.
	 * @param string        $hash             The stream hash.
	 * @param int           $page             The page number to fetch.
	 * @param int           $per_page         The number of items per page.
	 * @param string        $order_by         The field on which to order the results.
	 * @param string        $order_dir        The direction of the ordering.
	 * @param boolean       $include_finished True to include subscriptions against
	 * @param string        $hash_type        Stream hash or Historics playback id.
	 *                                        finished historic queries.
	 *
	 * @return array Of DataSift_Push_Subscription objects.
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_AccessDenied
	 */
	static public function listByStreamHash($user, $hash, $page = 1, $per_page = 20, $order_by = self::ORDERBY_CREATED_AT, $order_dir = self::ORDERDIR_ASC, $include_finished = false, $hash_type = false, $hash = false)
	{
		return $this->listSubscriptions($user, $page, $per_page, $order_by, $order_dir, $include_finished, 'hash', $hash);
	}
	
	/**
	 * Get a page of push subscriptions to the given stream playback_id, where
	 * each page contains up to per_page items. Results will be ordered
	 * according to the supplied ordering parameters.
	 * 
	 * @param DataSift_User $user             The user.
	 * @param string        $playback_id      The Historics playback ID.
	 * @param int           $page             The page number to fetch.
	 * @param int           $per_page         The number of items per page.
	 * @param string        $order_by         The field on which to order the results.
	 * @param string        $order_dir        The direction of the ordering.
	 * @param boolean       $include_finished True to include subscriptions against
	 * @param string        $hash_type        Stream hash or Historics playback id.
	 * @param string        $hash             The stream hash.
	 *                                        finished historic queries.
	 *
	 * @return array Of DataSift_Push_Subscription objects.
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_AccessDenied
	 */
	static public function listByPlaybackId($user, $playback_id, $page = 1, $per_page = 20, $order_by = self::ORDERBY_CREATED_AT, $order_dir = self::ORDERDIR_ASC, $include_finished = false, $hash_type = false, $hash = false)
	{
		return $this->listSubscriptions($user, $page, $per_page, $order_by, $order_dir, $include_finished, 'playback_id', $playback_id);
	}
	
    /**
     * Page through recent push subscription log entries, specifying the sort
     * order.
     * 
     * @param DataSift_User $user      The user making the request.
     * @param int           $page      Which page to fetch.
     * @param int           $per_page  Based on this page size.
     * @param string        $order_by  Which field to sort by.
     * @param string        $order_dir In asc[ending] or desc[ending] order.
     * @param string        $id        Push subscription ID.
     *
     * @return array Of LogEntry objects.
     * @throws DataSift_Exception_APIError 
     * @throws DataSift_Exception_InvalidData 
     * @throws DataSift_Exception_AccessDenied 
     */
    static public function getLogs($user, $page = 1, $per_page = 20, $order_by = self::ORDERBY_REQUEST_TIME, $order_dir = self::ORDERDIR_ASC, $id = false)
    {
		if ($page < 1) {
			throw new DataSift_Exception_InvalidData('The specified page number is invalid');
		}
		
		if ($per_page < 1) {
			throw new DataSift_Exception_InvalidData('The specified per_page value is invalid');
		}

    	$params = array();

		if ($id !== false && strlen($id) > 0) {
			$params['id'] = $id;
		}
		$params['page'] = $page;
		$params['per_page'] = $per_page;
		$params['order_by'] = $order_by;
		$params['order_dir'] = $order_dir;

		$res = $user->callAPI('push/log', $params);

		$retval = array('count' => $res['count'], 'log_entries' => array());
		foreach ($res['log_entries'] as $log) {
			$retval['log_entries'][] = new DataSift_Push_LogEntry($log);
		}
		
		return $retval;
    }

    /**
     * @var string The subscription ID.
     */
	protected $_id = '';
	
	/**
	 * @var int The timestamp when this subscription was created.
	 */
	protected $_created_at = null;
	
	/**
	 * @var string The name of this subscription.
	 */
	protected $_name = '';
	
	/**
	 * @var string The current status of this subscription.
	 */
	protected $_status = '';
	
	/**
	 * @var string The hash to which this subscription is subscribed.
	 */
	protected $_hash = '';
	
	/**
	 * @var String "stream" or "historic"
	 */
	protected $_hash_type = '';
	
	/**
	 * @var int The timestamp of the last push request.
	 */
	protected $_last_request = null;
	
	/**
	 * @var int The timestamp of the last successful push request.
	 */
	protected $_last_success = null;
	
	/**
	 * @var boolean True if this subscription has been deleted (becomes
	 *              read-only).
	 */
	protected $_deleted = false;
	
	/**
	 * Construct a DataSift_Push_Subscription object from an array.
	 * 
	 * @param DataSift_User $user The user that owns this subscription.
	 * @param array         $data The JSON object containing the subscription details.
	 *
	 * @throws DataSift_Exception_InvalidData
	 */
	public function __construct($user, $data)
	{
		parent::__construct($user);
		$this->init($data);
	}
	
	/**
	 * Extract data from an array.
	 * 
	 * @param array $data An array containing the subscription data.
	 *
	 * @throws DataSift_Exception_InvalidData
	 */
	protected function init($data)
	{
		if (!isset($data['id'])) {
			throw new DataSift_Exception_InvalidData('No id found');
		}
		$this->_id = $data['id'];
		
		if (!isset($data['name'])) {
			throw new DataSift_Exception_InvalidData('No name found');
		}
		$this->_name = $data['name'];
		
		if (!isset($data['created_at'])) {
			throw new DataSift_Exception_InvalidData('No created_at found');
		}
		$this->_created_at = $data['created_at'];
		
		if (!isset($data['status'])) {
			throw new DataSift_Exception_InvalidData('No status found');
		}
		$this->_status = $data['status'];
		
		if (!isset($data['hash_type'], $data)) {
			throw new DataSift_Exception_InvalidData('No hash_type found');
		}
		$this->_hash_type = $data['hash_type'];
		
		if (!isset($data['hash'])) {
			throw new DataSift_Exception_InvalidData('No hash found');
		}
		$this->_hash = $data['hash'];
		
		if (!isset($data['last_request'])) {
			$this->_last_request = 0;
		} else {
			$this->_last_request = $data['last_request'];
		}
		
		if (!isset($data['last_success'])) {
			$this->_last_success = 0;
		} else {
			$this->_last_success = $data['last_success'];
		}
		
		if (!isset($data['output_type'])) {
			throw new DataSift_Exception_InvalidData('No output_type found');
		}
		$this->_output_type = $data['output_type'];
		
		if (!isset($data['output_params'])) {
			throw new DataSift_Exception_InvalidData('No output_params found');
		}
		$this->_output_params = $this->parseOutputParams($data['output_params']);
	}

	/**
	 * Recursive method to parse the output_params as received from the API
	 * into the flattened, dot-notation used by the client libraries.
	 *
	 * @param array  $params The parameters to parse.
	 * @param string $prefix The current key prefix.
	 *
	 * @return array
	 */
	protected function parseOutputParams($params, $prefix = '')
	{
		$retval = array();
		foreach ($params as $key => $val) {
			if (is_array($val)) {
				$retval = array_merge($retval, $this->parseOutputParams($val, $prefix.$key.'.'));
			} else {
				$retval[$prefix.$key] = $val;
			}
		}
		return $retval;
	}
	
	/**
	 * Re-fetch this subscription from the API.
	 *  
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_AccessDenied
	 */
	public function reload()
	{
		init($this->_user->callAPI('push/get', array('id' => $this->getId())));
	}
	
	/**
	 * Get the subscription ID.
	 * 
	 * @return string
	 */
	public function getId()
	{
		return $this->_id;
	}
	
	/**
	 * Get the subscription name.
	 * 
	 * @return string
	 */
	public function getName()
	{
		return $this->_name;
	}
	
	/**
	 * Set an output parameter. Checks to see if the subscription has been
	 * deleted, and if not calls the base class to set the parameter.
	 * 
	 * @param string $key The output parameter to set.
	 * @param string $val The value to which to set it.
	 *
	 * @throws DataSift_Exception_InvalidData
	 */
	public function setOutputParam($key, $val)
	{
		if ($this->isDeleted()) {
			throw new DataSift_Exception_InvalidData('Cannot modify a deleted subscription');
		}
		parent::setOutputParam($key, $val);
	}

	/**
	 * Get the timestamp when this subscription was created.
	 * 
	 * @return int
	 */
	public function getCreatedAt()
	{
		return $this->_created_at;
	}

	/**
	 * Get the current status of this subscription. Make sure you call reload
	 * to get the latest data for this subscription first.
	 * 
	 * @return string
	 * @see self::STATUS_*
	 */
	public function getStatus()
	{
		return $this->_status;
	}
	
	/**
	 * Returns true if this subscription has been deleted.
	 * 
	 * @return boolean
	 */
	public function isDeleted()
	{
		return ($this->getStatus() == self::STATUS_DELETED);
	}
	
	/**
	 * Get the hash type to which this subscription is subscribed.
	 * 
	 * @return string
	 */
	public function getHashType()
	{
		return $this->_hash_type;
	}
	
	/**
	 * Get the hash or playback ID to which this subscription is subscribed.
	 * 
	 * @return string
	 */
	public function getHash() {
		return $this->_hash;
	}
	
	/**
	 * Get the output type.
	 * 
	 * @return string
	 */
	public function getOutputType() {
		return $this->_output_type;
	}
	
	/**
	 * Get the timestamp of the last push request.
	 * 
	 * @return int
	 */
	public function getLastRequest() {
		return $this->_last_request;
	}
	
	/**
	 * Get the timestamp of the last successful push request.
	 * 
	 * @return int
	 */
	public function getLastSuccess() {
		return $this->_last_success;
	}
	
	/**
	 * Save changes to the name and output_parameters of this subscription.
	 * 
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_AccessDenied
	 */
	public function save()
	{
		// Can only update the name and output_params
		$params = array('id' => $this->getId(), 'name' => $this->getName());
		foreach ($this->_output_params as $key => $val) {
			$params[DataSift_Push_Definition::OUTPUT_PARAMS_PREFIX.$key] = $val;
		}

		// Call the API and pass the returned object into init to update this object
		$this->init($this->_user->callAPI('push/update', $params));
	}
	
	/**
	 * Pause this subscription.
	 * 
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_AccessDenied
	 */
	public function pause()
	{
		$this->init($this->_user->callAPI('push/pause', array('id' => $this->getId())));
	}
	
	/**
	 * Resume this subscription.
	 * 
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_AccessDenied
	 */
	public function resume()
	{
		$this->init($this->_user->callAPI('push/resume', array('id' => $this->getId())));
	}
	
	/**
	 * Stop this subscription.
	 * 
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_AccessDenied
	 */
	public function stop()
	{
		$this->init($this->_user->callAPI('push/stop', array('id' => $this->getId())));
	}
	
	/**
	 * Delete this subscription.
	 * 
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_AccessDenied
	 */
	public function delete()
	{
		$this->_user->callAPI('push/delete', array('id' => $this->getId()));
		// The delete API call doesn't return the object, so set the status
		// manually
		$this->_status = self::STATUS_DELETED;
	}
	
	/**
	 * Get a page of the log for this subscription order as specified.
	 * 
	 * @param int    $page      The page to get.
	 * @param int    $per_page  The number of entries per page.
	 * @param string $order_by  By which field to order the entries. 
	 * @param string $order_dir The direction of the sorting ("asc" or "desc").
	 *
	 * @return array
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_AccessDenied
	 */
	public function getLog($page = 1, $per_page = 20, $order_by = self::ORDERBY_REQUEST_TIME, $order_dir = self::ORDERDIR_DESC)
	{
		return self::getLogs($this->_user, $page, $per_page, $order_by, $order_dir, $this->getId());
	}
}
