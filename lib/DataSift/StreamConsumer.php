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
 * The DataSift_StreamConsumer class is an abstract base class for various
 * stream consumers.
 *
 * @category DataSift
 * @package  PHP-client
 * @author   Stuart Dallas <stuart@3ft9.com>
 * @license  http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link     http://www.mediasift.com
 */
abstract class DataSift_StreamConsumer
{
	const TYPE_HTTP = 'HTTP';

	const STATE_STOPPED  = 0;
	const STATE_STARTING = 1;
	const STATE_RUNNING  = 2;
	const STATE_STOPPING = 3;

	/**
	 * @var DataSift_User
	 */
	protected $_user = null;

	/**
	 * @var DataSift_Definition
	 */
	protected $_definition = false;

	/**
	 * @var bool
	 */
	protected $_auto_reconnect = true;

	/**
	 * @var bool True if this is consuming multiple hashes
	 */
	protected $_is_multi = false;

	/**
	 * @var bool True if this is a historic consumer
	 */
	protected $_is_historic = false;

	/**
	 * @var array The array of hashes to be consumed if using multi
	 */
	protected $_hashes = array();

	/**
	 * @var bool
	 */
	protected $_state = self::STATE_STOPPED;

	/**
	 * @var DataSift_IStreamConsumerEventHandler The event handler object.
	 */
	protected $_eventHandler = false;

	/**
	 * Factory function. Creates a StreamConsumer-derived object for the given
	 * type.
	 *
	 * @param string $type         Use the TYPE_ constants
	 * @param mixed  $definition   CSDL string or a Definition object.
	 * @param string $eventHandler The object that will receive events.
	 *
	 * @return DataSift_StreamConsumer The consumer object
	 * @throws DataSift_Exception_InvalidData
	 */
	public static function factory($user, $type, $definition, $eventHandler)
	{
		$classname = 'DataSift_StreamConsumer_'.$type;
		if (!class_exists($classname)) {
			throw new DataSift_Exception_InvalidData('Consumer type "'.$type.'" is unknown');
		}

		return new $classname($user, $definition, $eventHandler);
	}

	/**
	 * Factory function for historic streams. Creates a StreamConsumer-derived
	 * object for the given type.
	 *
	 * @param string $type         Use the TYPE_ constants
	 * @param mixed  $definition   CSDL string or a Definition object.
	 * @param string $eventHandler The object that will receive events.
	 *
	 * @return DataSift_StreamConsumer The consumer object
	 * @throws DataSift_Exception_InvalidData
	 */
	public static function historicFactory($user, $type, $definition, $eventHandler)
	{
		$classname = 'DataSift_StreamConsumer_'.$type;
		if (!class_exists($classname)) {
			throw new DataSift_Exception_InvalidData('Consumer type "'.$type.'" is unknown');
		}

		return new $classname($user, $definition, $eventHandler, true);
	}

	/**
	 * Constructor. Do not use this directly, use the factory method instead.
	 *
	 * @param DataSift_User $user          The user this consumer will run as.
	 * @param mixed         $definition    CSDL string, a Definition object, or an array of hashes.
	 * @param string        $onInteraction The function to be called for each interaction.
	 * @param string        $onStopped     The function to be called when the consumer stops.
	 * @param string        $onDeleted     The function to be called for each DELETE request.
	 *
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSiftExceotion_CompileFailed
	 * @throws DataSift_Exception_APIError
	 */
	protected function __construct($user, $definition, $eventHandler, $historic = false)
	{
		if (!($user instanceof DataSift_User)) {
			throw new DataSift_Exception_InvalidData('Please supply a valid DataSift_User object when creating a DataSift_StreamConsumer object.');
		}

		if (is_array($definition) && count($definition) > 0) {
			// Yes, we're multi
			$this->_is_multi = true;
			// Get the hashes
			foreach ($definition as $d) {
				if ($d instanceof DataSift_Definition) {
					$this->_hashes[] = $d->getHash();
				} else {
					$this->_hashes[] = $d;
				}
			}
		} elseif (is_string($definition)) {
			// Convert the CSDL into a Definition object
			$this->_definition = $user->createDefinition($definition);
		} elseif ($definition instanceof DataSift_Definition) {
			// Already a Definition object
			$this->_definition = $definition;
		} elseif ($definition instanceof DataSift_Historic) {
			// Already a Historic object, implements the interface we need
			$this->_definition = $definition;
			// Override the historic flag
			$historic = true;
		} else {
			throw new DataSift_Exception_InvalidData('The definition must be a CSDL string, a DataSift_Definition object, or an array of stream hashes.');
		}

		// Set the user
		$this->_user = $user;

		// Validate and set the event handler
		if (!($eventHandler instanceof DataSift_IStreamConsumerEventHandler)) {
			throw new DataSift_Exception_InvalidData('Your event handler object must implement the DataSift_IStreamConsumerEventHandler interface.');
		}
		$this->_eventHandler = $eventHandler;

		// Ask for the definition hash - this will compile the definition if
		// necessary
		if (!$this->_is_multi) {
			$this->_definition->getHash();
		}

		// Set whether this is a historic query
		$this->_is_historic = $historic;
	}

	/**
	 * This is called when the underlying stream is connected.
	 *
	 * @return void
	 */
	protected function onConnect()
	{
		$this->_eventHandler->onConnect($this);
	}

	/**
	 * This is called for each interaction received from the stream and must
	 * be implemented in extending classes.
	 *
	 * @param array $interaction The interaction data structure
	 *
	 * @return void
	 */
	protected function onInteraction($interaction, $hash = false)
	{
		$this->_eventHandler->onInteraction($this, $interaction, $hash);
	}

	/**
	 * This is called for each DELETE request received from the stream and must
	 * be implemented in extending classes.
	 *
	 * @param array $interaction The interaction data structure
	 *
	 * @return void
	 */
	protected function onDeleted($interaction, $hash = false)
	{
		$this->_eventHandler->onDeleted($this, $interaction, $hash);
	}

	/**
	 * Called for each status message received from the stream.
	 *
	 * @param string $type The status type.
	 * @param array  $info The data received along with the status message.
	 *
	 * @return void
	 */
	protected function onStatus($type, $info = array())
	{
		$this->_eventHandler->onStatus($this, $type, $info);
	}

	/**
	 * This is called when an error notification is received on a stream
	 * connection.
	 *
	 * @param string $message The error message
	 *
	 * @return void
	 */
	protected function onError($message)
	{
		$this->_eventHandler->onError($this, $message);
	}

	/**
	 * This is called when a warning notification is received on a scream
	 * connection.
	 *
	 * @param string $message The warning message
	 *
	 * @return void
	 */
	protected function onWarning($message)
	{
		$this->_eventHandler->onWarning($this, $message);
	}

	/**
	 * This is called when the underlying stream is disconnected.
	 *
	 * @return void
	 */
	protected function onDisconnect()
	{
		$this->_eventHandler->onDisconnect($this);
	}

	/**
	 * This is called when the consumer is stopped.
	 *
	 * @param string $reason Reason to stop the stream
	 *
	 * @return void
	 */
	protected function onStopped($reason = '')
	{
		$this->_eventHandler->onStopped($this, $reason);
	}

	/**
	 * Once an instance of a StreamConsumer is ready for use, call this to
	 * start consuming. Extending classes should implement onStart to handle
	 * actually starting.
	 *
	 * @param boolean $auto_reconnect Whether to reconnect automatically
	 *
	 * @return void
	 */
	public function consume($auto_reconnect = true)
	{
		$this->_auto_reconnect = $auto_reconnect;

		// If this is a historic, start it
		if ($this->_is_historic) {
			$this->_definition->start();
		}

		// Start consuming
		$this->_state = self::STATE_STARTING;
		$this->onStart();
	}

	/**
	 * Called when the consumer should start consuming the stream.
	 *
	 *
	 * @return void
	 * @abstract
	 */
	abstract protected function onStart();

	/**
	 * This method can be called at any time to *request* that the consumer
	 * stop consuming. This method sets the state to STATE_STOPPING and it's
	 * up to the consumer implementation to notice that this has changed, stop
	 * consuming and call the onStopped method.
	 *
	 * @return void
	 * @throws DataSift_Exception_InalidData
	 */
	public function stop()
	{
		if ($this->_state != self::STATE_RUNNING) {
			throw new DataSift_Exception_InvalidData('Consumer state must be RUNNING before it can be stopped');
		}
		$this->_state = self::STATE_STOPPING;
	}

	/**
	 * Default implementation of onStop. It's unlikely that this method will
	 * ever be used in isolation, but rather it should be called as the final
	 * step in the extending class's implementation.
	 *
	 * @param string $reason Reason why the stream was stopped
	 *
	 * @return void
	 * @throws DataSift_Exception_InvalidData
	 */
	protected function onStop($reason = '')
	{
		//var_dump(debug_backtrace());
		if ($this->_state != self::STATE_STOPPING and $reason == '') {
			$reason = 'Unexpected';
		}

		$this->_state = self::STATE_STOPPED;
		$this->onStopped($reason);
	}
}
