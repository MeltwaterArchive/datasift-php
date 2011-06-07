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
	 * Constructor. A DataSift_User object is required, and you can optionally
	 * supply a default definition string.
	 *
	 * @param DataSift_User $user The user object.
	 * @param string        $csdl An optional default definition string.
	 *
	 * @throws DataSift_Exception_InvalidData
	 */
	public function __construct($user, $csdl = '', $hash = false)
	{
		if (!($user instanceof DataSift_User)) {
			throw new DataSift_Exception_InvalidData('Please supply a valid DataSift_User object when creating a DataSift_Definition object.');
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

		// If the string has changed, reset the hash
		if ($this->_csdl != $csdl) {
			$this->clearHash();
		}

		$this->_csdl = trim($csdl);
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
	 * Reset the hash to false. The effect of this is to make the definition
	 * as requiring compilation.
	 *
	 * @return void
	 */
	protected function clearHash()
	{
		$this->_hash = false;
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
		if (strlen(trim($this->_csdl)) == 0) {
			throw new DataSift_Exception_InvalidData('Cannot compile an empty definition.');
		}

		try {
			//$res = $this->_user->callAPI('stream/compile', array('stream_definition' => $this->_csdl));
			$res = $this->_user->callAPI('compile', array('csdl' => $this->_csdl));

			if (isset($res['hash'])) {
				$this->_hash = $res['hash'];
			} elseif (isset($res['stream_identifier'])) {
				$this->_hash = $res['stream_identifier'];
			} else {
				throw new DataSift_Exception_CompileFailed('Compiled successfully but no hash in the response');
			}
		} catch (DataSift_Exception_APIError $e) {
			// Reset the hash
			$this->clearHash();

			switch ($e->getCode()) {
				case 400:
					// Compilation failed, we should have an error message
					if (!empty($res['error'])) {
						throw new DataSift_Exception_CompileFailed($res['error']);
					}
					throw new DataSift_Exception_CompileFailed('No error message was provided');
					break;

				default:
					throw new DataSift_Exception_CompileFailed('Unexpected APIError code: '.$e->getCode().' ['.$e->getMessage().']');
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
	public function getConsumer($type = DataSift_StreamConsumer::TYPE_HTTP, $onInteraction = false, $onStopped = false)
	{
		return DataSift_StreamConsumer::factory($this->_user, $type, $this, $onInteraction, $onStopped);
	}
}
