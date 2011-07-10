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
 * The DataSift_StreamConsumer_HTTP class extends DataSift_StreamConsumer
 * and implements HTTP streaming.
 *
 * @category DataSift
 * @package  PHP-client
 * @author   Stuart Dallas <stuart@3ft9.com>
 * @license  http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link     http://www.mediasift.com
 */
class DataSift_StreamConsumer_HTTP extends DataSift_StreamConsumer
{
	/**
	 * @var resource The HTTP connection resource
	 */
	private $_conn = null;

	/**
	 * @var int Connect timeout in seconds
	 */
	protected $_connect_timeout = 30;

	/**
	 * @var int Read timeout in seconds
	 */
	protected $_read_timeout = 5;

	/**
	 * @var int Max allowable line size from the stream
	 */
	protected $_max_line_length = 65536;

	/**
	 * @var bool Automatically reconnect if the connection is dropped
	 */
	protected $_auto_reconnect = true;

	/**
	 * Constructor.
	 *
	 * @param DataSift_User $user          The authenticated user
	 * @param mixed         $definition    CSDL string or a Definition object
	 * @param mixed         $onInteraction A function name or array(class/object, method)
	 * @param mixed         $onStopped     A function name or array(class/object, method)
	 *
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exceotion_CompileFailed
	 * @throws DataSift_Exception_APIError
	 * @see DataSift_StreamConsumer::__construct
	 */
	public function __construct($user, $definition, $onInteraction = false, $onStopped = false)
	{
		parent::__construct($user, $definition, $onInteraction, $onStopped);
	}

	/**
	 * Set whether to automatically reconnect to the stream if the connection
	 * is dropped.
	 *
	 * @param bool $reconnect True to enable automatic reconnection
	 *
	 * @return void
	 */
	public function setAutoReconnect($reconnect)
	{
		$this->_auto_reconnect = (bool) $reconnect;
	}

	/**
	 * SetUp
	 *
	 * @return void
	 */
	protected function onStart()
	{
		do {
			// Disconnect and reconnect
			$this->reconnect();

			// Params for stream_select
			$read   = array($this->_conn);
			$write  = null;
			$except = null;

			$buf = '';

			// Loop while the connection is valid using stream_select with a
			// timeout. The timeout allows us to check to see if a stop has been
			// requested.
			while ($this->_conn && !feof($this->_conn) && ($count = stream_select($read, $write, $except, $this->_read_timeout))) {
				// Only continue if we're in the right state
				if ($this->_state != parent::STATE_RUNNING) {
					break;
				}

				if (!$count) {
					// Nothing waiting, listen again
					continue;
				}

				// Set the stream as blocking
				stream_set_blocking($this->_conn, 1);

				// Get the chunk length
				$len = fgets($this->_conn, $this->_max_line_length);
				if ($len === false) {
					// EOF
					continue;
				}

				// Read and process each line
				while ($this->_state == parent::STATE_RUNNING and ($line = fgets($this->_conn, $this->_max_line_length)) !== false) {
					if (strlen($line) == 0) {
						// End of the chunk
						break;
					}

					// Decode the JSON
					$interaction = json_decode(trim($line), true);

					// If the interaction is valid, pass it to the event handler
					if ($interaction) {
						// Ignore ticks
						if (!empty($interaction['interaction'])) {
							$this->onInteraction($interaction);
						}
					}
				}

				// Set the stream as non-blocking
				stream_set_blocking($this->_conn, 0);
			}
		} while ($this->_conn && !feof($this->_conn) and $this->_auto_reconnect and $this->_state == parent::STATE_RUNNING);

		// Make sure we're properly disconnected and in a known state
		$this->disconnect();

		// We've stopped for some reason, figure out why
		if ($this->_state == parent::STATE_STOPPING) {
			$reason = 'Stop requested';
		} else {
			$reason = 'Connection dropped';
		}

		// Now tell the user
		$this->onStop($reason);
	}

	/**
	 * Connect to the DataSift HTTP stream
	 *
	 * @return void
	 */
	private function connect()
	{
		$this->_state = parent::STATE_STARTING;

		// Build the URL and parse it
		$url = parse_url('http://'.DataSift_User::STREAM_BASE_URL.$this->_definition->getHash());

		// Fill in some defaults if any required bits are missing
		if (empty($url['port'])) {
			$url['port'] = 80;
		}

		// Build the request headers
		$request   = array();
		$request[] = 'GET '.$url['path'].'?api_key='.$this->_user->getAPIKey().' HTTP/1.1';
		$request[] = 'Host: '.$url['host'];
		$request[] = 'User-Agent: '.$this->_user->getUserAgent();
		$request[] = 'Accept: */*';

		$connection_delay = 0;

		do {
			// Back off a bit if required
			if ($connection_delay > 0) {
				sleep($connection_delay);
			}

			// Make the connection
			$this->_conn = fsockopen('tcp://'.$url['host'], $url['port'], $err, $errno, $this->_connect_timeout);

			// Catch failures
			if (!$this->_conn or !is_resource($this->_conn)) {
				// Connection failed
				$this->_conn = false;
			} else {
				// Blocking while we do the secret handshake
				stream_set_blocking($this->_conn, 1);

				// Send the request
				foreach ($request as $line) {
					fwrite($this->_conn, $line."\r\n");
				}

				// A blank line indicates the end of the request
				fwrite($this->_conn, "\r\n");

				// Read the response headers
				$response = array();
				$line = true;
				while ($line) {
					$line = trim(fgets($this->_conn, $this->_max_line_length));
					$response[] = $line;
				}
			}

			// If the connection failed or
			if ($this->_conn and count($response) == 1 and strlen(trim($response[0])) == 0) {
				// Connection failed or timed out
				// Timings from http://support.datasift.net/help/kb/rest-api/http-streaming-api
				if ($connection_delay == 0) {
					$connection_delay = 1;
				} elseif ($connection_delay < 16) {
					$connection_delay++;
				} else {
					throw new DataSift_Exception_StreamError('Connection failed due to a network error');
				}
			} else {
				// Check the first line to make sure it's a positive response
				list($http, $code, $message) = preg_split('|\s+|', $response[0], 3);

				if ($code == '200') {
					// Success!
					$this->_state = parent::STATE_RUNNING;
				} elseif ($code == '404') {
					// The hash doesn't exist
					throw new DataSift_Exception_StreamError('Hash not found!');
				} else {
					// Connection failed, back off a bit and try again
					// Timings from http://support.datasift.net/help/kb/rest-api/http-streaming-api
					if ($connection_delay == 0) {
						$connection_delay = 10;
					} elseif ($connection_delay < 240) {
						$connection_delay *= 2;
					} else {
						throw new DataSift_Exception_StreamError('Connection failed: '.$code.' '.$message);
					}
				}
			}
		} while ($this->_state != parent::STATE_RUNNING);

		// Set the stream as non-blocking
		stream_set_blocking($this->_conn, 0);
	}

	/**
	 * Disconnect from the DataSift stream
	 *
	 * @return void
	 */
	private function disconnect()
	{
		if (is_resource($this->_conn)) {
			fclose($this->_conn);
		}
		$this->_auto_reconnect = false;
		$this->_conn = null;
	}

	/**
	 * Reconnect to the DataSift stream
	 *
	 * @return void
	 */
	private function reconnect()
	{
		$auto = $this->_auto_reconnect;
		$this->disconnect();
		$this->auto_reconnect = $auto;
		$this->connect();
	}
}
