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
 * The DataSift_Push_LogEntry class represents a subscription log entry.
 *
 * @category DataSift
 * @package  PHP-client
 * @author   Stuart Dallas <stuart@3ft9.com>
 * @license  http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link     http://www.mediasift.com
 */
class DataSift_Push_LogEntry {
	/**
	 * @var string The subscription ID.
	 */
	private $_subscription_id = '';
	
	/**
	 * @var int The timestamp of the log entry.
	 */
	private $_request_time = null;
	
	/**
	 * @var boolean True if this entry is reporting a successful action.
	 */
	private $_success = false;
	
	/**
	 * @var string The log message.
	 */
	private $_message = '';
	
	/**
	 * Construct an instance from the data in an array.
	 * 
	 * @param JSONObject json The data.
	 *
	 * @throws JSONException
	 */
	public function __construct($data_or_subscription_id = false, $request_time = false, $success = null, $message = '')
	{
		if ($data === false) {
			throw new DataSift_Exception_InvalidData('Please provide a log entry for the constructor.');
		}

		if (is_array($data)) {
			if (!isset($data['request_time'])) {
				throw new DataSift_Exeption_InvalidData('No request timestamp in the log entry data.');
			}
			$request_time = $data['request_time'];

			if (!isset($data['success'])) {
				throw new DataSift_Exeption_InvalidData('No success indicator in the log entry data.');
			}
			$success = $data['success'];

			if (isset($data['message'])) {
				$message = $data['message'];
			}

			if (!isset($data['subscription_id'])) {
				throw new DataSift_Exeption_InvalidData('No subscription ID in the log entry data.');
			}
			$data_or_subscription_id = $data['subscription_id'];
		}

		$this->_subscription_id = $data_or_subscription_id;
		$this->_request_time    = intval($request_time);
		$this->_success         = (bool)$success;
		$this->_message         = $message;
	}
	
	/**
	 * Get the subscription ID.
	 * 
	 * @return string
	 */
	public function getSubscriptionId() {
		return $this->_subscription_id;
	}
	
	/**
	 * Get the request time.
	 * 
	 * @return int
	 */
	public function getRequestTime() {
		return $this->_request_time;
	}
	
	/**
	 * Get whether this entry is reporting a successful action.
	 * 
	 * @return boolean
	 */
	public function getSuccess() {
		return $this->_success;
	}
	
	/**
	 * Get the log message.
	 * 
	 * @return string
	 */
	public function getMessage() {
		return $this->_message;
	}
}
