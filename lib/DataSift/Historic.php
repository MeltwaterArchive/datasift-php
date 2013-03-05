<?php
/**
 * DataSift client
 *
 * This software is the intellectual property of MediaSift Ltd., and is covered
 * by retained intellectual property rights, including copyright.
 * Distribution of this software is strictly forbidden under the terms of this license.
 *
 * The DataSift_Historic class represents a historic query.
 *
 * @category  DataSift
 * @package   PHP-client
 * @author    Stuart Dallas <stuart@3ft9.com>
 * @copyright 2011 MediaSift Ltd.
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      http://www.mediasift.com
 */

/**
 * The DataSift_Historic class represents a historic query.
 *
 * @category  DataSift
 * @package   PHP-client
 * @author    Stuart Dallas <stuart@3ft9.com>
 * @copyright 2011 MediaSift Ltd.
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      http://www.mediasift.com
 */
class DataSift_Historic
{
	/**
	 * The default sample rate.
	 */
	const DEFAULT_SAMPLE = 100;

	/**
	 * List Historics queries.
	 *
	 * @param DataSift_User $user     The user object.
	 * @param int           $page     The start page.
	 * @param int           $per_page The start page.
	 *
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_APIError
	 */
	static public function listHistorics($user, $page = 1, $per_page = 20)
	{
		try {
			$res = $user->callAPI(
				'historics/get',
				array(
					'page' => $page,
					'max' => $page,
				)
			);

			$retval = array('count' => $res['count'], 'historics' => array());

			foreach ($res['data'] as $historic) {
				$retval['historics'][] = new self($user, $historic);
			}

			return $retval;
		} catch (DataSift_Exception_APIError $e) {
			switch ($e->getCode()) {
				case 400:
					// Missing or invalid parameters
					throw new DataSift_Exception_InvalidData($e->getMessage());

				default:
					throw new DataSift_Exception_APIError(
						'Unexpected APIError code: ' . $e->getCode() . ' [' . $e->getMessage() . ']'
					);
			}
		}
	}

	/**
	 * @var DataSift_User The user object.
	 */
	protected $_user = null;

	/**
	 * @var string Playback ID
	 */
	protected $_playback_id = false;

	/**
	 * @var string Number of DPUs consumed
	 */
	protected $_dpus = false;

	/**
	 * @var array Data availablility information.
	 */
	protected $_availability = false;

	/**
	 * @var string The stream hash.
	 */
	protected $_hash = false;

	/**
	 * @var int Start date and time
	 */
	protected $_start = false;

	/**
	 * @var int End date and time
	 */
	protected $_end = false;

	/**
	 * @var int Historics query creation time
	 */
	protected $_created_at = false;

	/**
	 * @var double Sample size
	 */
	protected $_sample = false;

	/**
	 * @var array Data sources.
	 */
	protected $_sources = array();

	/**
	 * @var string Historics query name.
	 */
	protected $_name = false;

	/**
	 * @var string Historics query status
	 */
	protected $_status = 'created';

	/**
	 * @var int Progress counter
	 */
	protected $_progress = 0;

	/**
	 * @var boolean Set to true if the Historics query has been deleted.
	 */
	protected $_deleted = false;

	/**
	 * @var integer The estimated completion timestamp
	 */
	protected $_estimated_completion = 0;

	/**
	 * Generate a name based on the current date/time.
	 *
	 * @return string The generated name.
	 */
	protected function generateName()
	{
		return 'historic_'.date('Y-m-d_H-i-s');
	}

	/**
	 * Constructor. Pass all fields to create a new historic, or provide a
	 * User object and a playback_id as the $hash parameter to load an
	 * existing query from the API.
	 *
	 * @param DataSift_User $user    The user object.
	 * @param string        $hash    The stream hash for the query.
	 * @param int           $start   The start timestamp.
	 * @param int           $end     The end timestamp.
	 * @param array         $sources The interaction types to match.
	 * @param string        $name    A name for this query.
	 * @param int           $sample  An optional sample rate for this query.
	 *
	 * @throws DataSift_Exception_InvalidData
	 */
	public function __construct($user, $hash, $start = false, $end = false, $sources = false, $name = false, $sample = 100)
	{
		if (!($user instanceof DataSift_User)) {
			throw new DataSift_Exception_InvalidData(
				'Please supply a valid DataSift_User object when creating a DataSift_Definition object.'
			);
		}
		$this->_user    = $user;

		// If $start is missing or false then we're getting a historic query
		// from the API.
		if ($start === false) {
			if (is_array($hash)) {
				// Initialising from an array
				$this->_playback_id = $hash['id'];
				$this->initFromArray($hash);
			} else {
				$this->_playback_id = $hash;
				$this->reloadData();
			}
		} else {
			// Creating a new historic query.
			if ($hash instanceof DataSift_Definition) {
				$hash = $hash->getHash();
			}

			if (intval($start) != $start) {
				$start = strtotime($start);
			}

			if (intval($end) != $end) {
				$end = strtotime($end);
			}

			if ($start == 0) {
				throw new DataSift_Exception_InvalidData(
					'Please supply a valid start timestamp.'
				);
			}

			if ($end == 0) {
				throw new DataSift_Exception_InvalidData(
					'Please supply a valid end timestamp.'
				);
			}

			if (empty($sources) || !is_array($sources)) {
				throw new DataSift_Exception_InvalidData(
					'Please supply a valid array of sources.'
				);
			}

			$this->_hash    = $hash;
			$this->_start   = $start;
			$this->_end     = $end;
			$this->_sources = $sources;
			$this->_name    = $name;
			$this->_sample  = $sample;
		}
	}

	/**
	 * Reload the data for this object from the API.
	 *
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_AccessDenied
	 */
	public function reloadData()
	{
		if ($this->_deleted) {
			throw new DataSift_Exception_InvalidData('Cannot reload the data for a deleted Historic.');
		}

		if ($this->_playback_id === false) {
			throw new DataSift_Exception_InvalidData('Cannot reload the data for a Historic with no playback ID.');
		}

		try {
			$this->initFromArray($this->_user->callAPI('historics/get', array('id' => $this->_playback_id)));
		} catch (DataSift_Exception_APIError $e) {
			switch ($e->getCode()) {
				case 400:
					// Missing or invalid parameters
					throw new DataSift_Exception_InvalidData($e->getMessage());

				default:
					throw new DataSift_Exception_APIError(
						'Unexpected APIError code: ' . $e->getCode() . ' [' . $e->getMessage() . ']'
					);
			}
		}
	}

	/**
	 * Initialise this object from the data in the given array.
	 *
	 * @param array $data The array of data.
	 *
	 * @throws DataSift_Exception_InvalidData
	 */
	protected function initFromArray($data)
	{
		if (!isset($data['id'])) {
			throw new DataSift_Exception_APIError('No playback ID in the response');
		}
		if ($data['id'] != $this->_playback_id) {
			throw new DataSift_Exception_APIError('Incorrect playback ID in the response');
		}

		if (!isset($data['definition_id'])) {
			throw new DataSift_Exception_APIError('No definition hash in the response');
		}
		$this->_hash = $data['definition_id'];

		if (!isset($data['name'])) {
			throw new DataSift_Exception_APIError('No name in the response');
		}
		$this->_name = $data['name'];

		if (!isset($data['start'])) {
			throw new DataSift_Exception_APIError('No start timestamp in the response');
		}
		$this->_start = $data['start'];

		if (!isset($data['end'])) {
			throw new DataSift_Exception_APIError('No end timestamp in the response');
		}
		$this->_end = $data['end'];

		if (!isset($data['created_at'])) {
			throw new DataSift_Exception_APIError('No created at timestamp in the response');
		}
		$this->_created_at = $data['created_at'];

		if (!isset($data['status'])) {
			throw new DataSift_Exception_APIError('No status in the response');
		}
		$this->_status = $data['status'];

		if (!isset($data['progress'])) {
			throw new DataSift_Exception_APIError('No progress in the response');
		}
		$this->_progress = $data['progress'];

		if (!isset($data['sources'])) {
			throw new DataSift_Exception_APIError('No sources in the response');
		}
		$this->_sources = $data['sources'];

		if (!isset($data['sample'])) {
			throw new DataSift_Exception_APIError('No smaple in the response');
		}
		$this->_sample = $data['sample'];

		if (isset($data['estimated_completion'])) {
			$this->_estimated_completion = $data['estimated_completion'];
		}

		if ($this->_status == 'deleted') {
			$this->_deleted = true;
		}
	}

	/**
	 * Returns the playback ID for this historic. If the historic has not yet
	 * been prepared that will be done automagically to obtain the ID.
	 *
	 * @return string The playback ID.
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_RateLimitExceeded
	 * @throws DataSift_Exception_InvalidData
	 */
	public function getHash()
	{
		if ($this->_playback_id === false) {
			$this->prepare();
		}
		return $this->_playback_id;
	}

	/**
	 * Returns the stream hash.
	 *
	 * @return string The hash.
	 */
	public function getStreamHash()
	{
		return $this->_hash;
	}

	/**
	 * Returns the DPU cost of running this historic. If the historic has not
	 * yet been prepared that will be done automagically to obtain the cost.
	 *
	 * @return int The DPU cost.
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_RateLimitExceeded
	 * @throws DataSift_Exception_InvalidData
	 */
	public function getDPUs()
	{
		if ($this->_dpus === false) {
			$this->prepare();
		}
		return $this->_dpus;
	}

	/**
	 * Returns the data availability information for this historic. If the
	 * historic has not yet been prepared that will be done automagically to
	 * obtain the cost.
	 *
	 * @return array The data availability.
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_RateLimitExceeded
	 * @throws DataSift_Exception_InvalidData
	 */
	public function getAvailability()
	{
		if ($this->_availability === false) {
			$this->prepare();
		}
		return $this->_availability;
	}

	/**
	 * Returns the start date.
	 *
	 * @return int The start date.
	 */
	public function getStartDate()
	{
		return $this->_start;
	}

	/**
	 * Returns the end date.
	 *
	 * @return int The end date.
	 */
	public function getEndDate()
	{
		return $this->_end;
	}

	/**
	 * Returns the created at date. To refresh this from the server call
	 * reloadData().
	 *
	 * @return int The created at date.
	 */
	public function getCreatedAt()
	{
		return $this->_created_at;
	}

	/**
	 * Returns the sources.
	 *
	 * @return int The start date.
	 */
	public function getSources()
	{
		return $this->_sources;
	}

	/**
	 * Returns the current progress.
	 *
	 * @return double The percent progress.
	 */
	public function getProgress()
	{
		return $this->_progress;
	}

	/**
	 * Returns the name.
	 *
	 * @return string The name.
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * Sets the name.
	 *
	 * @param string $name The new name.
	 *
	 * @return void
	 */
	public function setName($name)
	{
		if ($this->_deleted) {
			throw new DataSift_Exception_InvalidData('Cannot set the name of a deleted Historic.');
		}

		// Update locally if this query hasn't been prepared, otherwise send
		// it to the API.
		if ($this->_playback_id === false) {
			$this->_name = $name;
		} else {
			$res = $this->_user->callAPI(
				'historics/update',
				array(
					'id' => $this->_playback_id,
					'name' => $name,
				)
			);

			$this->reloadData();
		}
	}

	/**
	 * Returns the sample.
	 *
	 * @return double The sample.
	 */
	public function getSample()
	{
		return $this->_sample;
	}

	/**
	 * Returns the status. To refresh this from the server call
	 * reloadData().
	 *
	 * @return string The status.
	 */
	public function getStatus()
	{
		return $this->_status;
	}

	/**
	 * Returns the estimated completion in UTC timestamp
	 *
	 * @return integer
	 */
	public function getEstimatedCompletion()
	{
		return $this->_estimated_completion;
	}

	/**
	 * Call the DataSift API to prepare this historic query.
	 *
	 * @return void
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_InvalidData
	 */
	public function prepare()
	{
		if ($this->_deleted) {
			throw new DataSift_Exception_InvalidData('Cannot prepare a deleted Historic.');
		}

		if ($this->_playback_id !== false) {
			throw new DataSift_Exception_InvalidData('This historic query has already been prepared.');
		}

		try {
			$res = $this->_user->callAPI(
				'historics/prepare',
				array(
					'hash' => $this->_hash,
					'start' => $this->_start,
					'end' => $this->_end,
					'name' => $this->_name,
					'sources' => implode(',', $this->_sources),
					'sample' => $this->_sample,
				)
			);

			if (isset($res['id'])) {
				$this->_playback_id = $res['id'];
			} else {
				throw new DataSift_Exception_APIError('Prepared successfully but no playback ID in the response');
			}

			if (isset($res['dpus'])) {
				$this->_dpus = $res['dpus'];
			} else {
				throw new DataSift_Exception_APIError('Prepared successfully but no DPU cost in the response');
			}

			if (isset($res['availability'])) {
				$this->_availability = $res['availability'];
			} else {
				throw new DataSift_Exception_APIError('Prepared successfully but no availability in the response');
			}
		} catch (DataSift_Exception_APIError $e) {
			switch ($e->getCode()) {
				case 400:
					// Missing or invalid parameters
					throw new DataSift_Exception_InvalidData($e->getMessage());

				default:
					throw new DataSift_Exception_APIError(
						'Unexpected APIError code: ' . $e->getCode() . ' [' . $e->getMessage() . ']'
					);
			}
		}
	}

	/**
	 * Start this historic query.
	 *
	 * @return void
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_InvalidData
	 */
	public function start()
	{
		if ($this->_deleted) {
			throw new DataSift_Exception_InvalidData('Cannot start a deleted Historic.');
		}

		if ($this->_playback_id === false || strlen($this->_playback_id) == 0) {
			throw new DataSift_Exception_InvalidData('Cannot start a historic query that hasn\'t been prepared.');
		}

		try {
			$res = $this->_user->callAPI(
				'historics/start',
				array(
					'id' => $this->_playback_id,
				)
			);
		} catch (DataSift_Exception_APIError $e) {
			switch ($e->getCode()) {
				case 400:
					// Missing or invalid parameters
					throw new DataSift_Exception_InvalidData($e->getMessage());

				case 404:
					// Historic query not found
					throw new DataSift_Exception_InvalidData($e->getMessage());

				default:
					throw new DataSift_Exception_APIError(
						'Unexpected APIError code: ' . $e->getCode() . ' [' . $e->getMessage() . ']'
					);
			}
		}
	}

	/**
	 * Stop this historic query.
	 *
	 * @return void
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_InvalidData
	 */
	public function stop()
	{
		if ($this->_deleted) {
			throw new DataSift_Exception_InvalidData('Cannot stop a deleted Historic.');
		}

		if ($this->_playback_id === false || strlen($this->_playback_id) == 0) {
			throw new DataSift_Exception_InvalidData('Cannot stop a historic query that hasn\'t been prepared.');
		}

		try {
			$res = $this->_user->callAPI(
				'historics/stop',
				array(
					'id' => $this->_playback_id,
				)
			);
		} catch (DataSift_Exception_APIError $e) {
			switch ($e->getCode()) {
				case 400:
					// Missing or invalid parameters
					throw new DataSift_Exception_InvalidData($e->getMessage());

				case 404:
					// Historic query not found
					throw new DataSift_Exception_InvalidData($e->getMessage());

				default:
					throw new DataSift_Exception_APIError(
						'Unexpected APIError code: ' . $e->getCode() . ' [' . $e->getMessage() . ']'
					);
			}
		}
	}

	/**
	 * Delete this historic query.
	 *
	 * @return void
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_InvalidData
	 */
	public function delete()
	{
		if ($this->_deleted) {
			throw new DataSift_Exception_InvalidData('Cannot delete a deleted historic.');
		}

		if ($this->_playback_id === false || strlen($this->_playback_id) == 0) {
			throw new DataSift_Exception_InvalidData('Cannot delete a historic query that hasn\'t been prepared.');
		}

		try {
			$res = $this->_user->callAPI(
				'historics/delete',
				array(
					'id' => $this->_playback_id,
				)
			);
			$this->_deleted = true;
		} catch (DataSift_Exception_APIError $e) {
			switch ($e->getCode()) {
				case 400:
					// Missing or invalid parameters
					throw new DataSift_Exception_InvalidData($e->getMessage());

				case 404:
					// Historic query not found
					throw new DataSift_Exception_InvalidData($e->getMessage());

				default:
					throw new DataSift_Exception_APIError(
						'Unexpected APIError code: ' . $e->getCode() . ' [' . $e->getMessage() . ']'
					);
			}
		}
	}

	/**
	 * Get a page of push subscriptions for this historic query, where each
	 * page contains up to $per_page items. Results will be returned in the
	 * order requested.
	 *
	 * @param DataSift_User $user The user object.
   * @param int $page The page number to get.
   * @param int $per_page The number of items per page.
   * @param String $order_by  Which field to sort by.
   * @param String $order_dir In asc[ending] or desc[ending] order.
   * @param bool $include_finished Set to true when you want to include finished subscription in the results.
	 */
	public function getPushSubscriptions($user, $page = 1, $per_page = 20, $order_by = self::ORDERBY_CREATED_AT, $order_dir = self::ORDERDIR_ASC, $include_finished = false)
	{
		if ($this->_deleted) {
			throw new DataSift_Exception_InvalidData('Cannot get the push subscriptions for a deleted Historic.');
		}

		if ($this->_playback_id === false || strlen($this->_playback_id) == 0) {
			throw new DataSift_Exception_InvalidData('Cannot get the push subscriptions for a historic query that hasn\'t been prepared.');
		}

		return DataSift_Push_Subscription::listSubscriptions($this->_user, $page, $per_page, $order_by, $order_dir, $include_finished, $hash_type = 'playback_id', $this->_playback_id);
	}
}
