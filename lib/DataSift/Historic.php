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
 * The DataSift_Historic class represents a historic query.
 *
 * @category DataSift
 * @package  PHP-client
 * @author   Stuart Dallas <stuart@3ft9.com>
 * @license  http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link     http://www.mediasift.com
 */
class DataSift_Historic
{
	/**
	 * @var DataSift_User
	 */
	protected $_user = null;

	/**
	 * @var string
	 */
	protected $_playback_id = false;

	/**
	 * @var string
	 */
	protected $_dpus = false;

	/**
	 * @var string
	 */
	protected $_hash = false;

	/**
	 * @var int
	 */
	protected $_start = false;

	/**
	 * @var int
	 */
	protected $_end = false;

	/**
	 * @var string
	 */
	protected $_feeds = array();

	/**
	 * @var array
	 */
	protected $_name = false;

	/**
	 * Constructor. A DataSift_User object is required, and you can optionally
	 * supply a default definition string.
	 *
	 * @param DataSift_User $user The user object.
	 * @param string        $csdl An optional default definition string.
	 * @param string        $hash An optional hash for the passed definition.
	 *
	 * @throws DataSift_Exception_InvalidData
	 */
	public function __construct($user, $hash, $start, $end, $feeds, $name = false)
	{
		if (!($user instanceof DataSift_User)) {
			throw new DataSift_Exception_InvalidData(
				'Please supply a valid DataSift_User object when creating a DataSift_Definition object.'
			);
		}

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

		if (empty($feeds) || !is_array($feeds)) {
			throw new DataSift_Exception_InvalidData(
				'Please supply a valid array of feeds types.'
			);
		}

		if ($name === false) {
			$name = tempnam('', 'historic_');
		}

		$this->_user = $user;
		$this->_hash = $hash;
		$this->_start = $start;
		$this->_end = $end;
		$this->_feeds = $feeds;
		$this->_name = $name;
	}

	/**
	 * Returns the playback ID for this historic. If the historic has not yet
	 * been prepared that will be done automagically to obtain the ID.
	 *
	 * @return string The hash.
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
	 * Call the DataSift API to prepare this history query.
	 *
	 * @return void
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_InvalidData
	 */
	public function prepare()
	{
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
					'feed' => implode(',', $this->_feeds),
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
		if ($this->_playback_id === false || strlen($this->_playback_id) == 0) {
			throw new DataSift_Exception_InvalidData('Cannot start a historic that hasn\'t been prepared.');
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
	 * Returns a DataSift_StreamConsumer-derived object for this definition,
	 * for the given type.
	 *
	 * @param string $type The consumer type for which to construct a consumer.
	 *
	 * @return DataSift_StreamConsumer The consumer object.
	 * @throws DataSift_Exception_InvalidData
	 * @see DataSift_StreamConsumer
	 */
	public function getConsumer($type = DataSift_StreamConsumer::TYPE_HTTP, $onInteraction = false, $onStopped = false, $onDeleted = false)
	{
		return DataSift_StreamConsumer::historicFactory($this->_user, $type, $this, $onInteraction, $onStopped, $onDeleted);
	}
}
