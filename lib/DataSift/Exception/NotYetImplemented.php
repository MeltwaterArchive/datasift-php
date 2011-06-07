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
 * NotYetImplemented exception
 *
 * @category DataSift
 * @package  PHP-client
 * @author   Stuart Dallas <stuart@3ft9.com>
 * @license  http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link     http://www.mediasift.com
 * @see      Exception
 */
class DataSift_Exception_NotYetImplemented extends Exception
{
	/**
	 * Overrides the standard Exception handler to set a default message if
	 * none is provided.
	 *
	 * @param string    $message  Error message
	 * @param int       $code     Error code
	 * @param Exception $previous Previous exception
	 *
	 * @see Exception
	 */
	public function __construct($message = null, $code = 0, Exception $previous = null)
	{
		if (null === $message) {
			$message = 'Not yet implemented ('.$this->getFile().':'.$this->getLine().')';
		}

		parent::__construct($message, $code, $previous);
	}
}
