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
 * The DataSift_Recording class represents a single recording within the
 * user's account.
 *
 * @category DataSift
 * @package  PHP-client
 * @author   Stuart Dallas <stuart@3ft9.com>
 * @license  http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link     http://www.mediasift.com
 */
class DataSift_Recording
{
	/**
	 * @var DataSift_User
	 */
	protected $_user = null;

	/**
	 * @var int
	 */
	protected $_id = null;

	/**
	 * @var int
	 */
	protected $_start_time = null;

	/**
	 * @var int
	 */
	protected $_finish_time = null;

	/**
	 * @var string
	 */
	protected $_name = null;

	/**
	 * @var string
	 */
	protected $_hash = null;

	/**
	 * Constructor. A DataSift_User object is required, and you can optionally
	 * supply a default definition string.
	 *
	 * @param DataSift_User $user The user object.
	 * @param string        $id The ID of the recording, or an array
	 *                          representing the recording (as returned by the
	 *                          API.
	 *
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_APIError
	 */
	public function __construct($user, $recording_id)
	{
		if (!($user instanceof DataSift_User)) {
			throw new DataSift_Exception_InvalidData(
				'Please supply a valid DataSift_User object when creating a DataSift_Recording object.'
			);
		}

		$this->_user = $user;

		if (!is_array($recording_id)) {
			// Get the object from the API
			$recording_id = $this->_user->callAPI('recording', array('id' => $recording_id));
		}

		// We have data, validate it
		if (!isset($recording_id['id']) or !is_string($recording_id['id'])) {
			throw new DataSift_Exception_InvalidData(
				'Invalid/missing ID in the recording data.'
			);
		}
		if (!isset($recording_id['start_time']) or !is_numeric($recording_id['start_time']) or $recording_id['start_time'] < 0) {
			throw new DataSift_Exception_InvalidData(
				'Invalid/missing start_time in the recording data.'
			);
		}
		if (!isset($recording_id['name']) or !is_string($recording_id['name'])) {
			throw new DataSift_Exception_InvalidData(
				'Invalid/missing name in the recording data.'
			);
		}
		if (!isset($recording_id['hash']) or !is_string($recording_id['hash'])) {
			throw new DataSift_Exception_InvalidData(
				'Invalid/missing hash in the recording data.'
			);
		}

		// Everything checks out, initialise the object
		$this->_id = $recording_id['id'];
		$this->_start_time = $recording_id['start_time'];
		$this->_end_time = $recording_id['end_time'];
		$this->_name = $recording_id['name'];
		$this->_hash = $recording_id['hash'];
	}

	/**
	 * Returns the ID.
	 *
	 * @return string The ID.
	 */
	public function getID()
	{
		return $this->_id;
	}

	/**
	 * Returns the start time.
	 *
	 * @return int The start time.
	 */
	public function getStartTime()
	{
		return $this->_start_time;
	}

	/**
	 * Returns the end time.
	 *
	 * @return int The end time.
	 */
	public function getEndTime()
	{
		return $this->_end_time;
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
	 * Returns the hash.
	 *
	 * @return string The hash.
	 */
	public function getHash()
	{
		return $this->_hash;
	}
}
