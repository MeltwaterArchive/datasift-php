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
		 * @var bool
		 */
		protected $_deleted = false;

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
		 * Constructor. A DataSift_User object is required, as is either a
		 * recording ID or an array representing the data for a recording.
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

			$this->init($recording_id);
		}

		/**
		 * Initialise the object with the supplied data.
		 *
		 * @param array $data An array containing the ID, start_time, finish_time, name and hash.
		 *
		 * @throws DataSift_Exception_InvalidData
		 */
		protected function init($data)
		{
			// We have data, validate it
			$this->validateData($data);

			// Everything checks out, initialise the object
			$this->_id = $data['id'];
			$this->_start_time = $data['start_time'];
			$this->_finish_time = $data['finish_time'];
			$this->_name = $data['name'];
			$this->_hash = $data['hash'];
		}

		/**
		 * Validate a set of data. Pass false as the second parameter to disable
		 * checking to make sure all parts of a recording are present.
		 *
		 * @param array $data An array containing all or part of the recording data.
		 * @param bool $check_for_missing_values Set to false to ignore missing data.
		 *
		 * @throws DataSift_Exception_InvalidData
		 */
		protected function validateData($data, $check_for_missing_values = true)
		{
			if ($check_for_missing_values)
			{
				if (!isset($data['id'])) {
					throw new DataSift_Exception_InvalidData('Missing ID in the recording data.');
				}
				if (!isset($data['start_time'])) {
					throw new DataSift_Exception_InvalidData('Missing start_time in the recording data.');
				}
				if (!isset($data['finish_time']) and !is_null($data['finish_time'])) {
					throw new DataSift_Exception_InvalidData('Missing finish_time in the recording data.');
				}
				if (!isset($data['name'])) {
					throw new DataSift_Exception_InvalidData('Missing name in the recording data.');
				}
				if (!isset($data['hash'])) {
					throw new DataSift_Exception_InvalidData('Missing hash in the recording data.');
				}
			}

			if (isset($data['id']) and !is_string($data['id'])) {
				throw new DataSift_Exception_InvalidData('Invalid ID in the recording data.');
			}

			if (isset($data['start_time']) and (!is_numeric($data['start_time']) or $data['start_time'] < 0)) {
				throw new DataSift_Exception_InvalidData('Invalid start_time in the recording data.');
			}

			if (isset($data['name']) and !is_string($data['name'])) {
				throw new DataSift_Exception_InvalidData('Invalid name in the recording data.');
			}

			if (isset($data['hash']) and !is_string($data['hash'])) {
				throw new DataSift_Exception_InvalidData('Invalid hash in the recording data.');
			}
		}

		/**
		 * Throw an exception if this recording has been deleted.
		 *
		 * @throws DataSift_Exception_InvalidData
		 */
		protected function checkDeleted()
		{
			if ($this->_deleted) {
				throw new DataSift_Exception_InvalidData('This recording has been deleted!');
			}
		}

		/**
		 * Returns the ID.
		 *
		 * @return string The ID.
		 */
		public function getID()
		{
			$this->checkDeleted();
			return $this->_id;
		}

		/**
		 * Returns the start time.
		 *
		 * @return int The start time.
		 */
		public function getStartTime()
		{
			$this->checkDeleted();
			return $this->_start_time;
		}

		/**
		 * Returns the end time.
		 *
		 * @return int The end time.
		 */
		public function getEndTime()
		{
			$this->checkDeleted();
			return $this->_finish_time;
		}

		/**
		 * Returns the name.
		 *
		 * @return string The name.
		 */
		public function getName()
		{
			$this->checkDeleted();
			return $this->_name;
		}

		/**
		 * Returns the hash.
		 *
		 * @return string The hash.
		 */
		public function getHash()
		{
			$this->checkDeleted();
			return $this->_hash;
		}

		/**
		 * Update the recording data.
		 *
		 * @param array $data An array containing the data to update (name, start_time and/or finish_time).
		 *
		 * @throws DataSift_Exception_InvalidData
		 * @throws DataSift_Exception_APIError
		 */
		public function update($data)
		{
			$this->checkDeleted();

			// Make sure we've been passed an array
			if (!is_array($data)) {
				throw new DataSift_Exception_InvalidData('The data passed in for an update must be an array');
			}

			// Initialise the parameters
			$params = array(
				'name' => false,
				'start' => false,
				'end' => false,
			);

			// Validate the data
			$this->validateData($data, false);

			// Pull data out of the array provided
			if (isset($data['name'])) {
				$params['name'] = $data['name'];
				unset($data['name']);
			}

			if (isset($data['start_time'])) {
				$params['start'] = $data['start_time'];
				unset($data['start_time']);
			}

			if (isset($data['finish_time'])) {
				$params['end'] = $data['finish_time'];
				unset($data['finish_time']);
			}

			if (count($data) > 0) {
				throw new DataSift_Exception_InvalidData('Unexpected data for update: '.implode(', ', array_keys($data)));
			}

			// Add the ID to the parameters
			$params['id'] = $this->_id;

			// Make the call
			$recording = $this->_user->callAPI('recording/update', $params);

			// Update this object with the results
			$this->init($recording);
		}

		/**
		 * Delete this recording.
		 *
		 * @throws DataSift_Exception_AccessDenied
		 * @throws DataSift_Exception_RateLimitExceeded
		 * @throws DataSift_Exception_ApiError
		 */
		public function delete()
		{
			$this->checkDeleted();

			$res = $this->_user->callAPI('recording/delete', array('id' => $this->_id));

			if (!isset($res['success']) or $res['success'] != 'true') {
				throw new DataSift_Exception_ApiError('Delete operation failed', -1);
			}

			$this->_deleted = true;
		}

		/**
		 * Start a new export of the data contained within this recording.
		 *
		 * @param string $format The format for the export. Use one of the DataSift_RecordingExport::FORMAT_* constants.
		 * @param string $name An optional name for the export.
		 * @param int $start An optional start timestamp.
		 * @param int $end An optional end timestamp.
		 *
		 * @throws DataSift_Exception_AccessDenied
		 * @throws DataSift_Exception_RateLimitExceeded
		 * @throws DataSift_Exception_ApiError
		 */
		public function startExport($format = DataSift_RecordingExport::FORMAT_JSON, $name = false, $start = false, $end = false)
		{
			$this->checkDeleted();

			$params = array('recording_id' => $this->_id);

			// Check the format
			if (!in_array($format, array(DataSift_RecordingExport::FORMAT_JSON, DataSift_RecordingExport::FORMAT_XLS, DataSift_RecordingExport::FORMAT_XLSX))) {
				throw new DataSift_Exception_InvalidData('Invalid export format specified');
			}
			$params['format'] = $format;

			// Check the name is valid if provided
			if ($name !== false) {
				if (!is_string($name) or strlen($name) == 0) {
					throw new DataSift_Exception_InvalidData('The export name must be a non-empty string');
				}
				$params['name'] = $name;
			}

			// Check the start parameter
			if ($start !== false) {
				if (intval($start) != $start or $start <= 0) {
					throw new DataSift_Exception_InvalidData('The start timestamp must be a positive integer');
				}
				if ($start < $this->_start_time) {
					throw new DataSift_Exception_InvalidData('The start timestamp must be equal to or greater than the recording start timestamp');
				}
				if ($start >= $this->_finish_time) {
					throw new DataSift_Exception_InvalidData('The start timestamp must be less than the recording finish timestamp');
				}
				$params['start'] = $start;
			}

			// Check the end parameter
			if ($end !== false) {
				if (intval($end) != $end or $end <= 0) {
					throw new DataSift_Exception_InvalidData('The end timestamp must be a positive integer');
				}
				if ($end > $this->_finish_time) {
					throw new DataSift_Exception_InvalidData('The end timestamp must be less than or equal to the recording finish timestamp');
				}
				if ($start !== false and $end < $start) {
					throw new DataSift_Exception_InvalidData('The end timestamp must be greater than the start timestamp');
				}
				$params['end'] = $end;
			}

			$res = $this->_user->callAPI('recording/export/start', $params);

			return new DataSift_RecordingExport($this->_user, $res);
		}
	}
