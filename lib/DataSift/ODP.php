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
	* @var DataSIft_User $user The user this DataSift_ODP belong to
	*/
	private $_user;

	/**
	* @var string The ODP Managed Source ID being used
	*/
	private $_source_id;

	/**
	* @var the data set being sent
	*/
	private $_data_set;


	/**
	* Construct the DataSift_ODP user object
	*
	* @param DataSift_User $user The Datasift user object
	* @param array $data Data used to populate the attributes of this object
	*
	*/
	public function __construct($user, $source_id, $data_set = array())
	{
		$this->_user = $user;
		$this->_source_id = $source_id;
		$this->_data_set = $data_set;
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
	* Sets the Data Set to be sent
	*
	* @param string $data_set the Managed Source ID
	*/
	public function setParams($data_set)
	{
		$this->_data_set = $data_set;
	}

	/**
	* Gets the Data Set
	*
	* @return string The Data to be sent in the curl request
	*/
	public function getParams()
	{
		return $this->_data_set;
	}

	/**
	* Generates a curl request to the Ingestion Endpoint 
	*/
	public function ingest()
	{
		if (strlen($this->_source_id) == 0) {
			echo new DataSift_Exception_InvalidData('Cannot initiate curl request without a source ID');
		}
		$data = '';
		foreach ($this->getParams() as $param) {
			$data .= json_encode($param)."\n";
		}
		
		return $this->_user->ingest($this->getSourceId(), $data);
	}
}


?>