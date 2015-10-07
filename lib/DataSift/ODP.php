<?php
/**
 * DataSift client
 *
 * This software is the intellectual property of MediaSift Ltd., and is covered
 * by retained intellectual property rights, including copyright.
 * Distribution of this software is strictly forbidden under the terms of this license.
 *
 * The DataSift_ODP class represents an ODP query.
 *
 * @category  DataSift
 * @package   PHP-client
 * @author    Ryan Stanley <ryan.stanley@datasift.com>
 * @copyright 2015 MediaSift Ltd.
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      http://www.mediasift.com
 */

class DataSift_ODP
{
	/**
	* @var DataSift_User $user The user this DataSift_ODP belong to
	*/
	private $_user;

	/**
	* @var string The ODP Managed Source ID being used
	*/
	private $_source_id;


	/**
	* Construct the DataSift_ODP user object
	*
	* @param DataSift_User $user The Datasift user object
	* @param String $source_ID The managed source ID used for ODP
	*
	*/
	public function __construct($user, $source_id)
	{
		$this->_user = $user;
		$this->_source_id = $source_id;
	}

	/**
	* Sets the Managed Source ID
	*
	* @param string $source_id the Managed Source ID
	*/
	public function setSourceId($source_id)
	{
		$this->_source_id = trim($source_id);
	}	

	/**
	* Gets the Managed Source ID
	*
	* @return string The Source ID to be used 
	*/
	public function getSourceId()
	{
		return $this->_source_id;
	}

	/**
	* Generates a curl request to the Ingestion Endpoint 
	*/
	public function ingest($data_set)
	{
		if (strlen($this->_source_id) == 0) {
			throw new DataSift_Exception_InvalidData('Cannot initiate curl request without a source ID');
		}

		if (empty($data_set)) {
			throw new DataSift_Exception_InvalidData('Cannot initiate curl request without a valid data set');
		}
		
		return $this->_user->ingest($this->getSourceId(), $data_set);
	}
}
?>
