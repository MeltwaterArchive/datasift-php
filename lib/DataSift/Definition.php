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
 * The DataSift_Definition class represents a stream definition.
 * 
 * @category DataSift
 * @package  PHP-client
 * @author   Stuart Dallas <stuart@3ft9.com>
 * @license  http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link     http://www.mediasift.com
 */
class DataSift_Definition
{
	/**
	 * @var DataSift_User
	 */
	protected $_user = null;

	/**
	 * @var string
	 */
	protected $_csdl = '';

	/**
	 * @var string
	 */
	protected $_hash = false;

	/**
	 * @var int
	 */
	protected $_created_at = false;

	/**
	 * @var int
	 */
	protected $_total_cost = false;

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
	public function __construct($user, $csdl = '', $hash = false)
	{
		if (!($user instanceof DataSift_User)) {
			throw new DataSift_Exception_InvalidData(
				'Please supply a valid DataSift_User object when creating a DataSift_Definition object.'
			);
		}

		$this->_user = $user;
		$this->_hash = $hash;
		$this->set($csdl);
	}

	/**
	 * Returns the definition string.
	 *
	 * @return string The definition.
	 */
	public function get()
	{
		return $this->_csdl;
	}

	/**
	 * Sets the definition string.
	 *
	 * @param string $csdl The new definition string.
	 *
	 * @return void
	 * @throws DataSift_Exception_InvalidData
	 */
	public function set($csdl)
	{
		if (!is_string($csdl)) {
			throw new DataSift_Exception_InvalidData('Definitions must be strings.');
		}

		// Trim the incoming string
		$csdl = trim($csdl);

		// If the string has changed, reset the hash
		if ($this->_csdl != $csdl) {
			$this->clearHash();
		}

		$this->_csdl = $csdl;
	}

	/**
	 * Returns the hash for this definition. If the hash has not yet been
	 * obtained it compiles the definition first.
	 *
	 * @return string The hash.
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_RateLimitExceeded
	 * @throws DataSift_Exception_InvalidData
	 */
	public function getHash()
	{
		if ($this->_hash === false) {
			// Catch any compilation errors so they don't pass up to the caller
			try {
				$this->compile();
			} catch (DataSift_Exception_CompileFailed $e) {
			}
		}
		return $this->_hash;
	}

	/**
	 * Reset the hash to false. The effect of this is to mark the definition
	 * as requiring compilation. Also resets other variables that depend on
	 * the CSDL.
	 *
	 * @return void
	 */
	protected function clearHash()
	{
		$this->_hash       = false;
		$this->_created_at = false;
		$this->_total_cost = false;
	}

	/**
	 * Returns the date when the stream was first created. If the created at
	 * date has not yet been obtained it validates the definition first.
	 *
	 * @return int The date as a unix timestamp.
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_RateLimitExceeded
	 * @throws DataSift_Exception_InvalidData
	 */
	public function getCreatedAt()
	{
		if ($this->_created_at === false) {
			// Catch any compilation errors so they don't pass up to the caller
			try {
				$this->validate();
			} catch (DataSift_Exception_CompileFailed $e) {
			}
		}
		return $this->_created_at;
	}

	/**
	 * Returns the total cost of the stream. If the cost has not yet been
	 * obtained it validates the definition first.
	 *
	 * @return int The date as a unix timestamp.
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_RateLimitExceeded
	 * @throws DataSift_Exception_InvalidData
	 */
	public function getTotalCost()
	{
		if ($this->_total_cost === false) {
			// Catch any compilation errors so they don't pass up to the caller
			try {
				$this->validate();
			} catch (DataSift_Exception_CompileFailed $e) {
			}
		}
		return $this->_total_cost;
	}

	/**
	 * Call the DataSift API to compile this defintion. On success it will
	 * store the returned hash.
	 *
	 * @return void
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_RateLimitExceeded
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_CompileFailed
	 */
	public function compile()
	{
		if (strlen($this->_csdl) == 0) {
			throw new DataSift_Exception_InvalidData('Cannot compile an empty definition.');
		}

		try {
			$res = $this->_user->callAPI('compile', array('csdl' => $this->_csdl));

			if (isset($res['hash'])) {
				$this->_hash = $res['hash'];
			} else {
				throw new DataSift_Exception_CompileFailed('Compiled successfully but no hash in the response');
			}

			if (isset($res['created_at'])) {
				$this->_created_at = strtotime($res['created_at']);
			} else {
				throw new DataSift_Exception_CompileFailed('Compiled successfully but no created_at in the response');
			}

			if (isset($res['cost'])) {
				$this->_total_cost = $res['cost'];
			} else {
				throw new DataSift_Exception_CompileFailed('Compiled successfully but no cost in the response');
			}
		} catch (DataSift_Exception_APIError $e) {
			// Reset the hash
			$this->clearHash();

			switch ($e->getCode()) {
				case 400:
					// Compilation failed, we should have an error message
					throw new DataSift_Exception_CompileFailed($e->getMessage());
					break;
				default:
					throw new DataSift_Exception_CompileFailed(
						'Unexpected APIError code: ' . $e->getCode() . ' [' . $e->getMessage() . ']'
					);
			}
		}
	}

	/**
	 * Call the DataSift API to validate this defintion. On success it will
	 * store the returned hash.
	 *
	 * @return void
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_RateLimitExceeded
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_CompileFailed
	 */
	public function validate()
	{
		if (strlen($this->_csdl) == 0) {
			throw new DataSift_Exception_InvalidData('Cannot validate an empty definition.');
		}

		try {
			$res = $this->_user->callAPI('validate', array('csdl' => $this->_csdl));

			if (isset($res['created_at'])) {
				$this->_created_at = strtotime($res['created_at']);
			} else {
				throw new DataSift_Exception_CompileFailed('Compiled successfully but no created_at in the response');
			}

			if (isset($res['cost'])) {
				$this->_total_cost = $res['cost'];
			} else {
				throw new DataSift_Exception_CompileFailed('Compiled successfully but no cost in the response');
			}
		} catch (DataSift_Exception_APIError $e) {
			// Reset the hash
			$this->clearHash();

			switch ($e->getCode()) {
				case 400:
					// Compilation failed, we should have an error message
					if (!empty($res['error'])) {
						throw new DataSift_Exception_CompileFailed($res['error']);
					} else {
						throw new DataSift_Exception_CompileFailed('No error message was provided');
					}
					break;

				default:
					throw new DataSift_Exception_CompileFailed('Unexpected APIError code: '.$e->getCode().' ['.$e->getMessage().']');
			}
		}
	}

	/**
	 * Call the DataSift API to get the cost for this definition. Returns an
	 * array containing...
	 *   costs => The breakdown of running the rule
	 *   total => The total cost of the rule
	 *
	 * @return array
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_CompileError
	 */
	public function getCostBreakdown()
	{
		$retval = false;

		if (strlen(trim($this->_csdl)) == 0) {
			throw new DataSift_Exception_InvalidData('Cannot get the cost for an empty definition.');
		}

		$retval = $this->_user->callAPI('cost', array('hash' => $this->getHash()));
		$this->_total_cost = $retval['total'];
		return $retval;
	}

	/**
	 * Call the DataSift API to get buffered interactions.
	 *
	 * @param int $count Optional number of interactions to return (max 200).
	 * @param int $from_id Optional start ID.
	 *
	 * @return array
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_CompileError
	 */
	public function getBuffered($count = false, $from_id = false)
	{
		$retval = false;

		if (strlen(trim($this->_csdl)) == 0) {
			throw new DataSift_Exception_InvalidData('Cannot get buffered interactions for an empty definition.');
		}

		$params = array('hash' => $this->getHash());
		if ($count !== false) {
			$params['count'] = $count;
		}
		if ($from_id !== false) {
			$params['interaction_id'] = $from_id;
		}

		$retval = $this->_user->callAPI('stream', $params);

		if (isset($retval['stream'])) {
			$retval = $retval['stream'];
		} else {
			throw new DataSift_Exception_APIError('No data in the response');
		}

		return $retval;
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
	public function getConsumer($type = DataSift_StreamConsumer::TYPE_HTTP, $onInteraction = false, $onStopped = false)
	{
		return DataSift_StreamConsumer::factory($this->_user, $type, $this, $onInteraction, $onStopped);
	}
}