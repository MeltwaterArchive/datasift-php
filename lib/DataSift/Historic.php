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
	protected $_sources = array();

	/**
	 * @var array
	 */
	protected $_name = false;

	/**
	 * Constructor.
	 *
	 * @param DataSift_User $user    The user object.
	 * @param string        $hash    The stream hash for the query.
	 * @param int           $start   The start timestamp.
	 * @param int           $end     The end timestamp.
	 * @param array         $sources The interaction types to match.
	 * @param string        $name    A name for this query.
	 *
	 * @throws DataSift_Exception_InvalidData
	 */
	public function __construct($user, $hash, $start, $end, $sources, $name)
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

		if (empty($sources) || !is_array($sources)) {
			throw new DataSift_Exception_InvalidData(
				'Please supply a valid array of sources.'
			);
		}

		$this->_user = $user;
		$this->_hash = $hash;
		$this->_start = $start;
		$this->_end = $end;
		$this->_sources = $sources;
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
	 * Call the DataSift API to prepare this historic query.
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
					'sources' => implode(',', $this->_sources),
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
}
