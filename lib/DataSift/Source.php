<?php
/**
 * DataSift client
 *
 * This software is the intellectual property of MediaSift Ltd., and is covered
 * by retained intellectual property rights, including copyright.
 * Distribution of this software is strictly forbidden under the terms of this license.
 *
 * The DataSift_Source class represents a private source
 *
 * @category  DataSift
 * @package   PHP-client
 * @author    Christopher Hoult <chris.hoult@datasift.com>
 * @copyright 2013 MediaSift Ltd.
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      http://www.mediasift.com
 */

/**
 * The DataSift_Historic class represents a historic query.
 *
 * @category  DataSift
 * @package   PHP-client
 * @author    Christopher Hoult <chris.hoult@datasift.com>
 * @copyright 2013 MediaSift Ltd.
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      http://www.mediasift.com
 */
class DataSift_Source
{

	/**
	 * Possible Source statuses
	 */
	const STATUS_ACTIVE = 'active';
	const STATUS_PAUSED = 'paused';
	const STATUS_DISABLED = 'disabled';
	const STATUS_DELETED = 'deleted';
	const STATUS_FAILED = 'failed';
	const STATUS_PROBLEMATIC = 'problematic';

	/**
	 * Gets a page of Sources where each page contains up to $perPage items.
	 *
	 * @param DataSift_User $user       The user making the request.
	 * @param integer       $page       The page number to fetch.
	 * @param integer       $perPage    The number of items per page.
	 * @param string|false  $sourceType The type of source to filter by; false for no filter
	 *
	 * @return array An array containing the number of sources returned in 'count' and a list of DataSift_Sources in 'sources'
	 *
	 * @throws DataSift_Exception_InvalidData
	 * @throws DataSift_Exception_APIError
	 */
	public static function listSources(DataSift_User $user, $page = 1, $perPage = 25, $sourceType = false)
	{
		try {
			$params = array(
				'page' => $page,
				'per_page' => $perPage,
			);

			if ($sourceType !== false) {
				$params['source_type'] = $sourceType;
			}

			$res = $user->callAPI(
				'source/get',
				$params
			);

			$retval = array('count' => $res['count'], 'sources' => array());

			foreach ($res['sources'] as $source) {
				$retval['sources'][] = new self($user, $source);
			}

			return $retval;
		} catch (DataSift_Exception_APIError $e) {
			switch ($e->getCode()) {
				case 400:
					// Missing or invalid parameters
					throw new DataSift_Exception_InvalidData($e->getMessage());

				default:
					throw new DataSift_Exception_APIError(
						'Unexpected APIError code: ' . $e->getCode() . ' [' . $e->getMessage() . ']'
					);
			}
		}
	}

	/**
	 * Gets a single DataSift_Source object by ID
	 *
	 * @param DataSift_User $user The user making the request.
	 * @param string        $id   The id of the Source to fetch
	 *
	 * @return DataSift_Source
	 *
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_AccessDenied
	 */
	public static function get(DataSift_User $user, $id)
	{
		$params = array('id' => $id);
		return new self($user, $user->callAPI('source/get', $params));
	}

	/**
	 * @var DataSift_User $user The user this DataSift_Source belongs to
	 */
	private $user;

	/**
	 * @var string The unique identifier of this DataSift_Source
	 */
	private $id;

	/**
	 * @var string The name of the type of source this object represents
	 */
	private $sourceType;

	/**
	 * @var string The current status of this Source
	 */
	private $status = self::STATUS_PAUSED;

	/**
	 * @var string The name of this Source
	 */
	private $name;

	/**
	 * @var array An array of authorization credentials for this Source.
	 *            Each item in the array should be an array of credentials
	 *            appropriate to the requested source type.
	 */
	private $auth = array();

	/**
	 * @var array An array resources this Source is subscribed to.
	 *
	 *            Each item in the array should be an array of resource
	 *            identifiers appropriate to the requested source type.
	 */
	private $resources = array();

	/**
	 * @var array The parameters for this Source
	 */
	private $parameters = array();

	/**
	 * @var integer The timestamp for this Source's creation date/time
	 */
	private $createdAt;

	/**
	 * Constructs a new DataSift_Source
	 *
	 * @param DataSift_User $user
	 * @param array $data
	 */
	public function __construct(DataSift_User $user, array $data = array())
	{
		$this->setUser($user);
		$this->fromArray($data);
	}

	/**
	 * Gets the user this DataSift_Source belongs to
	 *
	 * @return DataSift_User
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * Sets the user this DataSift_Source belongs to
	 *
	 * @param DataSift_User $user
	 */
	public function setUser(DataSift_User $user)
	{
		$this->user = $user;
	}

	/**
	 * Gets this DataSift_Source's unique identifier
	 *
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Sets this DataSift_Source's unique identifier
	 *
	 * @param string $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * Gets the type of this DataSift_Source
	 *
	 * @return string
	 */
	public function getSourceType()
	{
		return $this->sourceType;
	}

	/**
	 * Sets the type of this DataSift_Source
	 *
	 * @param string $sourceType
	 */
	public function setSourceType($sourceType)
	{
		$this->sourceType = $sourceType;
	}

	/**
	 * Gets the status of this DataSift_Source
	 *
	 * @return string
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * Sets the status of this DataSift_Source
	 *
	 * @param string $status A status from one of the allowed Source statuses
	 */
	public function setStatus($status)
	{
		$this->status = $status;
	}

	/**
	 * Gets the name of this DataSift_Source
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Sets the name of this DataSift_Source
	 *
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * Sets the authorization credentials for this DataSift_Source
	 *
	 * @return array
	 */
	public function getAuth()
	{
		return $this->auth;
	}

	/**
	 * Sets the authorization credentials for this DataSift_Source
	 *
	 * @param array $auth An array of authorization credentials, appropriate to
	 *                    the Source type
	 */
	public function setAuth(array $auth)
	{
		$this->auth = $auth;
	}

	/**
	 * Adds authorization credentials for this DataSift_Source using the auth/add
	 *
	 * @param array $auth An array of authorization credentials, appropriate to
	 *                    the Source type
	 */
	public function addAuth(array $auth, $validate=false){
		$response = $this->getUser()->callAPI('source/auth/add', array('id' => $this->getId(), 'auth' => $auth, 'validate' => $validate));

		$this->auth = $response['auth'];
	}

	/**
	 * Removes authorization credentials for this DataSift_Source using the auth/remove
	 *
	 * @param array $authIds An array of authorization IDs to be removed
	 */
	public function removeAuth(array $authIds){
		$response = $this->getUser()->callAPI('source/auth/remove', array('id' => $this->getId(), 'auth_ids' => $authIds));

		$this->auth = $response['auth'];
	}

	/**
	 * Gets the resources for this DataSift_Source
	 *
	 * @return array
	 */
	public function getResources()
	{
		return $this->resources;
	}

	/**
	 * Sets the resources for this DataSift_Source
	 *
	 * @param array $resources An array of resource definitions, appropriate to
	 *                         the Source type
	 */
	public function setResources(array $resources)
	{
		$this->resources = $resources;
	}

	/**
	 * Adds resources for this DataSift_Source using the resource/add
	 *
	 * @param array $resources An array of authorization credentials, appropriate to
	 *                    the Source type
	 */
	public function addResource(array $resources, $validate=false){
		$response = $this->getUser()->callAPI('source/resource/add', array('id' => $this->getId(), 'resources' => $resources, 'validate' => $validate));

		$this->resources = $response['resources'];
	}

	/**
	 * Removes resources for this DataSift_Source using the resource/remove
	 *
	 * @param array $resourceIds An array of resource IDs to be removed
	 */
	public function removeResource(array $resourceIds){
		$response = $this->getUser()->callAPI('source/resource/remove', array('id' => $this->getId(), 'resource_ids' => $resourceIds));

		$this->resources = $response['resources'];
	}

	/**
	 * Gets the parameters for this DataSift_Source
	 *
	 * @return array
	 */
	public function getParameters()
	{
		return $this->parameters;
	}

	/**
	 * Sets the parameters for this DataSift_Source, appropriate to the Source type
	 *
	 * @param array $parameters
	 */
	public function setParameters(array $parameters)
	{
		$this->parameters = $parameters;
	}

	/**
	 * Gets the created-at timestamp
	 *
	 * @return integer A UNIX timestamp
	 */
	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	/**
	 * Sets the created-at timestamp
	 *
	 * @param integer $createdAt
	 */
	public function setCreatedAt($createdAt)
	{
		$this->createdAt = $createdAt;
	}

	/**
	 * Save this Source
	 *
	 * @return DataSift_Source
	 *
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_AccessDenied
	 */
	public function save()
	{
		$endpoint = ($this->getId() ? 'source/update' : 'source/create');
		$this->fromArray($this->getUser()->callAPI($endpoint, $this->toArray()));

		return $this;
	}

	/**
	 * Stop this Source
	 *
	 * @return DataSift_Source
	 *
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_AccessDenied
	 */
	public function stop()
	{
		$this->fromArray($this->getUser()->callAPI('source/stop', array('id' => $this->getId())));

		return $this;
	}

	/**
	 * Start this Source
	 *
	 * @return DataSift_Source
	 *
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_AccessDenied
	 */
	public function start()
	{
		$this->fromArray($this->getUser()->callAPI('source/start', array('id' => $this->getId())));
	}

	/**
	 * Delete this Source.
	 *
	 * @return DataSift_Source
	 *
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_AccessDenied
	 */
	public function delete()
	{
		$this->getUser()->callAPI('source/delete', array('id' => $this->getId()));
		$this->setStatus(self::STATUS_DELETED);

		return $this;
	}

	/**
	 * Hydrates this Source from an array of API responses
	 *
	 * @param array $data
	 *
	 * @return DataSift_Source
	 */
	public function fromArray(array $data)
	{
		$map = array(
			'id' => 'setId',
			'name' => 'setName',
			'source_type' => 'setSourceType',
			'status' => 'setStatus',
			'parameters' => 'setParameters',
			'auth' => 'setAuth',
			'resources' => 'setResources',
			'created_at' => 'setCreatedAt'
		);

		foreach ($map as $key => $setter) {
			if (isset($data[$key])) {
				$this->$setter($data[$key]);
			}
		}

		return $this;
	}

	/**
	 * Converts this Source to an array suitable for transmission to the API
	 *
	 * @return array
	 */
	public function toArray()
	{
		$data = array();
		$map = array(
			'id' => 'getId',
			'name' => 'getName',
			'source_type' => 'getSourceType',
			'status' => 'getStatus',
			'parameters' => 'getParameters',
			'auth' => 'getAuth',
			'resources' => 'getResources',
			'created_at' => 'getCreatedAt'
		);

		foreach ($map as $key => $getter) {
			$data[$key] = $this->$getter();
		}

		foreach (array('auth', 'resources', 'parameters') as $key) {
			if (isset($data[$key])) {
				$data[$key] = json_encode($data[$key]);
			}
		}

		return $data;
	}

}