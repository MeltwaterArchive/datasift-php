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
 * The DataSift_Push_Definition class defines a push endpoint.
 *
 * @category DataSift
 * @package  PHP-client
 * @author   Stuart Dallas <stuart@3ft9.com>
 * @license  http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link     http://www.mediasift.com
 */
class DataSift_Push_Definition
{
	/**
	 * The prefix to be used when passing the output_params to API calls.
	 */
	const OUTPUT_PARAMS_PREFIX = 'output_params.';

	/**
	 * @var DataSift_User The user that owns this push definition.
	 */
	protected $_user = null;
	
	/**
	 * @var string An initial status for push subscriptions.
	 * @see PushSubscription::STATUS_*
	 */
	protected $_initial_status = '';
	
	/**
	 * @var string The output_type of this push definition.
	 */
	protected $_output_type = '';
	
	/**
	 * @var array The output parameters.
	 */
	protected $_output_params = array();
	
	/**
	 * Constructor. Takes the user creating the object.
	 * 
	 * @param DataSift_User $user The user creating this object.
	 */
	public function __construct($user = false)
	{
		if ($user === false) {
			throw new DataSift_Exception_InvalidData('A user object is required when constructing a DataSift_Push_Definition object');
		}
		$this->_user = $user;
	}
	
	/**
	 * Get the initial status for subscriptions.
	 * 
	 * @return string
	 * @see PushSubscription.STATUS_*
	 */
	public function getInitialStatus()
	{
		return $this->_initial_status;
	}
	
	/**
	 * Set the initial status for subscriptions.
	 * 
	 * @param string $status The initial status.
	 *
	 * @see PushSubscription.STATUS_*
	 */
	public function setInitialStatus($status)
	{
		$this->_initial_status = $status;
	}
	
	/**
	 * Get the output type.
	 * 
	 * @return string
	 */
	public function getOutputType()
	{
		return $this->_output_type;
	}
	
	/**
	 * Set the output type.
	 * 
	 * @param string $type The output type.
	 */
	public function setOutputType($type)
	{
		$this->_output_type = $type;
	}
	
	/**
	 * Set an output parameter.
	 * 
	 * @param string $key The output parameter to set.
	 * @param string $val The value to set it to.
	 *
	 * @throws DataSift_Exception_InvalidData
	 */
	public function setOutputParam($key, $val)
	{
		$this->_output_params[$key] = $val;
	}

	/**
	 * Get an output parameter.
	 * 
	 * @param string $key The parameter to get.
	 *
	 * @return string
	 */
	public function getOutputParam($key)
	{
		if (isset($this->_output_params[$key])) {
			return $this->_output_params[$key];
		}
		return null;
	}
	
	/**
	 * Get all of the output parameters.
	 * 
	 * @return array
	 */
	public function getOutputParams()
	{
		return $this->_output_params;
	}
	
	/**
	 * Validate the output type and parameters with the DataSift API.
	 * 
	 * @throws DataSift_Exception_AccessDenied
	 * @throws DataSift_Exception_InvalidData
	 */
	public function validate()
	{
		$retval = false;

		$params = array('output_type' => $this->_output_type);
		foreach ($this->_output_params as $key => $val) {
			$params[self::OUTPUT_PARAMS_PREFIX.$key] = $val;
		}

		try {
			$retval = $this->_user->callAPI('push/validate', $params);
		} catch (DataSift_Exception_APIError $e) {
			throw new DataSift_Exception_InvalidData($e->getMessage());
		}
	}
	
	/**
	 * Subscribe this endpoint to a Definition.
	 * 
	 * @param DataSift_Definition $definition The definition to which to subscribe.
	 * @param string              $name       A name for this subscription.
	 *
	 * @return DataSift_PushSubscription      The new subscription.
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_AccessDenied
	 * @throws DataSift_Exception_APIError
	 */
	public function subscribeDefinition($definition, $name)
	{
		return $this->subscribeStreamHash($definition->getHash(), $name);
	}
	
	/**
	 * Subscribe this endpoint to a stream hash.
	 * 
	 * @param string $hash               The has to which to subscribe.
	 * @param string $name               A name for this subscription.
	 *
	 * @return DataSift_PushSubscription The new subscription.
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_AccessDenied
	 */
	public function subscribeStreamHash($hash, $name) {
		return $this->subscribe('hash', $hash, $name);
	}
	
	/**
	 * Subscribe this endpoint to a Historic.
	 * 
	 * @param DataSift_Historic $historic The historic object to which to subscribe.
	 * @param string            $name     A name for this subscription.
	 *
	 * @return DataSift_PushSubscription  The new subscription.
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_AccessDenied
	 * @throws DataSift_Exception_APIError
	 */
	public function subscribeHistoric($historic, $name)
	{
		return $this->subscribeHistoricPlaybackId($historic->getHash(), $name);
	}
	
	/**
	 * Subscribe this endpoint to a historic playback ID.
	 * 
	 * @param string $playback_id         The playback ID.
	 * @param string $name                A name for this subscription.
	 *
	 * @return DataSift_PushSubscription  The new subscription.
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_AccessDenied
	 */
	public function subscribeHistoricPlaybackId($playback_id, $name)
	{
		return $this->subscribe('playback_id', $playback_id, $name);
	}
	
	/**
	 * Subscribe this endpoint to a stream hash or historic playback ID. Note
	 * that this will activate the subscription if the initial status is set
	 * to active.
	 * 
	 * @param string $hash_type          "hash" or "playback_id"
	 * @param string $hash               The hash or playback ID.
	 * @param string $name               A name for this subscription.
	 *
	 * @return DataSift_PushSubscription The new subscription.
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_AccessDenied
	 */
	protected function subscribe($hash_type, $hash, $name)
	{
		$retval = false;

		// API call parameters
		$params = array(
			'name' => $name,
			$hash_type => $hash,
			'output_type' => $this->_output_type,
		);
		// Prefix the output parameters
		foreach ($this->_output_params as $key => $val) {
			$params[self::OUTPUT_PARAMS_PREFIX.$key] = $val;
		}
		// Add the initial status if it's not empty
		if (strlen($this->getInitialStatus()) > 0) {
			$params['initial_status'] = getInitialStatus();
		}

		// Call the API and create a new PushSubscription from the returned
		// object
		return new DataSift_Push_Subscription($this->_user, $this->_user->callAPI('push/create', $params));
	}
}
