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
	 * The DataSift_RecordingExport class represents a single export within the
	 * user's account.
	 *
	 * @category DataSift
	 * @package  PHP-client
	 * @author   Stuart Dallas <stuart@3ft9.com>
	 * @license  http://www.debian.org/misc/bsd.license BSD License (3 Clause)
	 * @link     http://www.mediasift.com
	 */
	class DataSift_RecordingExport
	{
		/**
		 * Format constants
		 */
		const FORMAT_JSON = 'json';
		const FORMAT_XLS = 'xls';
		const FORMAT_XLSX = 'xlsx';

		/**
		 * Status constants
		 */
		const STATUS_SETUP = 'setup';
		const STATUS_PREP = 'prep';
		const STATUS_RUNNING = 'running';
		const STATUS_SUSPENDED = 'suspended';
		const STATUS_SUCCEEDED = 'succeeded';
		const STATUS_FAILED = 'failed';
		const STATUS_KILLED = 'killed';

		/**
		 * @var bool
		 */
		protected $_deleted = false;

		/**
		 * @var DataSift_User
		 */
		protected $_user = null;

		/**
		 * @var DataSift_Recording
		 */
		protected $_recording = null;

		/**
		 * @var string
		 */
		protected $_id = null;

		/**
		 * @var string
		 */
		protected $_recording_id = null;

		/**
		 * @var string
		 */
		protected $_name = null;

		/**
		 * @var int
		 */
		protected $_start = null;

		/**
		 * @var int
		 */
		protected $_finish = null;

		/**
		 * @var string
		 */
		protected $_status = 'unknown';

		/**
		 * Constructor. A DataSift_User object is required, as is either an
		 * export ID or an array representing the data for an export_id.
		 *
		 * @param DataSift_User $user The user object.
		 * @param string/array  $export The ID of the export, or an array
		 *                              representing the export (as returned by
		 *                              the API.
		 *
		 * @throws DataSift_Exception_InvalidData
		 * @throws DataSift_Exception_APIError
		 */
		public function __construct($user, $export)
		{
			if (!($user instanceof DataSift_User)) {
				throw new DataSift_Exception_InvalidData(
					'Please supply a valid DataSift_User object when creating a DataSift_RecordingExport object.'
				);
			}

			$this->_user = $user;

			if (!is_array($export)) {
				// Get the object from the API
				$export = $this->_user->callAPI('recording/export', array('id' => $export));
			}

			$this->init($export);
		}

		/**
		 * Initialise the object with the supplied data.
		 *
		 * @param array $data An array containing the ID, recording ID, name, start, end and status.
		 *
		 * @throws DataSift_Exception_InvalidData
		 */
		protected function init($data)
		{
			// We have data, validate it
			$this->validateData($data);

			// Everything checks out, initialise the object
			$this->_id = $data['id'];
			$this->_recording_id = $data['recording_id'];
			$this->_name = $data['name'];
			$this->_start = $data['start'];
			$this->_end = $data['end'];
			$this->_status = $data['status'];
		}

		/**
		 * Validate a set of data. Pass false as the second parameter to disable
		 * checking to make sure all parts of a recording are present.
		 *
		 * @param array $data An array containing all or part of the recording data.
		 *
		 * @throws DataSift_Exception_InvalidData
		 */
		protected function validateData($data, $check_for_missing_values = true)
		{
			if ($check_for_missing_values)
			{
				if (!isset($data['id'])) {
					throw new DataSift_Exception_InvalidData('Missing ID in the export data.');
				}
				if (!isset($data['recording_id'])) {
					throw new DataSift_Exception_InvalidData('Missing recording ID in the export data.');
				}
				if (!isset($data['name'])) {
					throw new DataSift_Exception_InvalidData('Missing name in the export data.');
				}
				if (!isset($data['start'])) {
					throw new DataSift_Exception_InvalidData('Missing start in the export data.');
				}
				if (!isset($data['end']) and !is_null($data['end'])) {
					throw new DataSift_Exception_InvalidData('Missing end in the export data.');
				}
				if (!isset($data['status'])) {
					throw new DataSift_Exception_InvalidData('Missing status in the export data.');
				}
			}

			if (isset($data['id']) and !is_string($data['id'])) {
				throw new DataSift_Exception_InvalidData('Invalid ID in the export data.');
			}

			if (isset($data['recording_id']) and !is_string($data['recording_id'])) {
				throw new DataSift_Exception_InvalidData('Invalid recording ID in the export data.');
			}

			if (isset($data['name']) and !is_string($data['name'])) {
				throw new DataSift_Exception_InvalidData('Invalid name in the export data.');
			}

			if (isset($data['start']) and (!is_numeric($data['start']) or $data['start'] < 0)) {
				throw new DataSift_Exception_InvalidData('Invalid start in the export data.');
			}

			if (isset($data['status']) and !is_string($data['status'])) {
				throw new DataSift_Exception_InvalidData('Invalid status in the export data.');
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
				throw new DataSift_Exception_InvalidData('This export has been deleted!');
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
		 * Returns the recording ID.
		 *
		 * @return string The recording ID.
		 */
		public function getRecordingID()
		{
			$this->checkDeleted();
			return $this->_recording_id;
		}

		/**
		 * Returns the start time.
		 *
		 * @return int The start time.
		 */
		public function getStart()
		{
			$this->checkDeleted();
			return $this->_start;
		}

		/**
		 * Returns the end time.
		 *
		 * @return int The end time.
		 */
		public function getEnd()
		{
			$this->checkDeleted();
			return $this->_end;
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
		public function getStatus()
		{
			$this->checkDeleted();
			return $this->_status;
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

			$res = $this->_user->callAPI('recording/export/delete', array('id' => $this->_id));

			if (!isset($res['success']) or $res['success'] != 'true') {
				throw new DataSift_Exception_ApiError('Delete operation failed', -1);
			}

			$this->_deleted = true;
		}
	}






















































