<?php
/**
 * DataSift client
 *
 * This software is the intellectual property of MediaSift Ltd., and is covered
 * by retained intellectual property rights, including copyright.
 * Distribution of this software is strictly forbidden under the terms of this license.
 *
 * The DataSift_HistoricPreview class represents a private source
 *
 * @category  DataSift
 * @package   PHP-client
 * @author    Courtney Robinson <courtney.robinson@datasift.com>
 * @copyright 2013 MediaSift Ltd.
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      http://www.mediasift.com
 */
class DataSift_HistoricPreview
{
	/**
	 * @var DataSift_User $user The user this DataSift_HistoricPreview belongs to
	 */
	private $user;
	/**
	 * @var string The unique identifier of this DataSift_HistoricPreview
	 */
	private $id;
	/**
	 * @var integer The timestamp for this Source's creation date/time
	 */
	private $createdAt;
	/**
	 * @var int $start time stamp to start the report from
	 */
	private $start;
	/**
	 * @var     int $end timestamp at which to end the report.
	 */
	private $end;
	/**
	 * @var string hash the hash of the stream to filter with
	 */
	private $hash;
	/**
	 * @var string parameters a list of at least one but no more than 20 historic preview parameters
	 */
	private $parameters;

	private $progress;

	private $status;

	private $feeds;

	private $sample;

	private $data;

	/**
	 * Constructs a new DataSift_HistoricPreview
	 *
	 * @param DataSift_User $user
	 * @param array $data
	 */
	public function __construct(DataSift_User $user, array $data = array())
	{
		$this->setUser($user);
		$this->fromResponse($data);
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
	 * Gets this DataSift_HistoricPreview's unique identifier
	 *
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Sets this DataSift_HistoricPreview's unique identifier
	 *
	 * @param string $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * Gets the user this DataSift_HistoricPreview belongs to
	 *
	 * @return DataSift_User
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * Sets the user this DataSift_HistoricPreview belongs to
	 *
	 * @param DataSift_User $user
	 */
	public function setUser(DataSift_User $user)
	{
		$this->user = $user;
	}

	/**
	 * Gets the timestamp this report starts at
	 *
	 * @return string
	 */
	public function getStart()
	{
		return $this->start;
	}

	/**
	 * Sets the timestamp this report starts
	 *
	 * @param string $start
	 */
	public function setStart($start)
	{
		$this->start = $start;
	}

	/**
	 * @return string
	 */
	public function getHash()
	{
		return $this->hash;
	}

	/**
	 * @param string $hash
	 */
	public function setHash($hash)
	{
		$this->hash = $hash;
	}

	/**
	 * Gets the parameters for this DataSift_HistoricPreview
	 *
	 * @return array
	 */
	public function getParameters()
	{
		return $this->parameters;
	}

	/**
	 * Sets the parameters for this DataSift_HistoricPreview
	 *
	 * @param array $parameters
	 */
	public function setParameters($parameters)
	{
		$this->parameters = is_string($parameters)? explode(',',$parameters) : $parameters;
	}

	/**
	 * Gets the timestamp this report ends at
	 *
	 * @return string
	 */
	public function getEnd()
	{
		return $this->end;
	}

	/**
	 * Sets the timestamp this report ends
	 *
	 * @param string $end
	 */
	public function setEnd($end)
	{
		$this->end = $end;
	}

	public function getProgress()
	{
		return $this->progress;
	}

	protected function setProgress($progress)
	{
		$this->progress= $progress;
	}

	public function getStatus()
	{
		return $this->status;
	}

	protected function setStatus($status)
	{
		$this->status= $status;
	}

	public function getFeeds()
	{
		return $this->feeds;
	}

	protected function setFeeds($feeds)
	{
		$this->feeds = $feeds;
	}

	public function getSample()
	{
		return $this->sample;
	}

	protected function setSample($sample)
	{
		$this->sample = $sample;
	}

	public function getData()
	{
		return $this->data;
	}

	protected function setData($data)
	{
		$this->data = $data;
	}

	/**
	 * Hydrates this preview from an array of API responses
	 *
	 * @param array $data
	 *
	 * @return DataSift_HistoricPreview
	 */
	public function fromResponse(array $data)
	{
		$map = array(
			'id' => 'setId',
			'start' => 'setStart',
			'end' => 'setEnd',
			'hash' => 'setHash',
			'parameters' => 'setParameters',
			'created_at' => 'setCreatedAt',
			//only present in responses
			'progress'=>'setProgress',
			'status' => 'setStatus',
			'feeds' => 'setFeeds',
			'sample' => 'setSample',
			'data' => 'setData',
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
			'start' => 'getStart',
			'end' => 'getEnd',
			'hash' => 'getHash',
			'parameters' => 'getParameters',
			'created_at' => 'getCreatedAt'
		);

		foreach ($map as $key => $getter) {
			$data[$key] = $this->$getter();
		}
		return $data;
	}

	/**
	 * Create the preview represented by the parameters in this object
	 *
	 * @return DataSift_HistoricPreview
	 *
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_AccessDenied
	 */
	public function create()
	{
		$params = array(
			'start' => $this->getStart(),
			'hash' => $this->getHash(),
			'parameters' => implode(',', $this->getParameters())
		);
		if (is_int($this->end)) {
			$params['end'] = $this->getEnd();
		}
		$this->fromResponse($this->getUser()->post('preview/create', $params));
	}

	/**
	 * Gets a single DataSift_HistoricPreview object by ID
	 *
	 * @param DataSift_User $user The user making the request.
	 * @param string $id   The id of the Source to fetch
	 *
	 * @return DataSift_HistoricPreview
	 *
	 * @throws DataSift_Exception_APIError
	 * @throws DataSift_Exception_AccessDenied
	 */
	public static function get(DataSift_User $user, $id)
	{
		$params = array('id' => $id);
		return new self($user, $user->post('preview/get', $params));
	}
}