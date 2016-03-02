<?php
/**
 * DataSift client
 *
 * This software is the intellectual property of MediaSift Ltd., and is covered
 * by retained intellectual property rights, including copyright.
 * Distribution of this software is strictly forbidden under the terms of this license.
 *
 * The DataSift_Pylon class represents a private source
 *
 * @category  DataSift
 * @package   PHP-client
 * @author    Paul Mozo <paul.mozo@datasift.com>
 * @copyright 2013 MediaSift Ltd.
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      http://www.mediasift.com
 */
class DataSift_Pylon
{
    /**
     * Order by constants.
     */
    const ORDERBY_ID           = 'id';
    const ORDERBY_CREATED_AT   = 'created_at';
    const ORDERBY_REQUEST_TIME = 'request_time';

    /**
     * Order direction constants.
     */
    const ORDERDIR_ASC  = 'asc';
    const ORDERDIR_DESC = 'desc';

    /**
     * @var DataSift_User $user The user this DataSift_Pylon belongs to
     */
    private $_user;

    /**
     * @var string The name of the DataSift_Pylon
     */
    private $_name;

    /**
     * @var string The CSDL of this DataSift_Pylon
     */
    private $_csdl;

    /**
     * @var string The stream hash of this DataSift_Pylon
     */
    private $_hash;

    /**
     * @var string The ID of this DataSift_Pylon recording
     */
    private $_id;

    /**
     * @var string The status of this DataSift_Pylon
     */
    private $_status;

    /**
     * @var int The time stamp of when the DataSift_Pylon started recording data
     */
    private $_start;

    /**
     * @var int The time stamp of when the DataSift_Pylon stopped recording data
     */
    private $_end;

    /**
     * @var int The amount of interactions recorded
     */
    private $_volume;

    /**
     * @var boolean Whether this subscription has reached capacity
     */
    private $_reached_capacity;

    /**
     * @var int The remaining capacity for this subscription
     */
    private $_remaining_index_capacity;

    /**
     * @var int The remaining account capacity
     */
    private $_remaining_account_capacity;

    /**
     * @var int The time stamp of when this definition was created
     */
    private $_created_at;

    /**
     * @var float The amount of interactions recorded
     */
    private $_dpu;

    /**
     * Construct the Datasift_Pylon object
     *
     * @param Datasift_User $user The Datasift user object
     * @param array $data Data used to populate the attributes of this object
     *
     * @return DataSift_Pylon
     */
    public function __construct($user, $data = false)
    {
        $this->_user = $user;

        if ($data) {
            $this->load($data);
        }
    }

    /**
     * Class method to find a Subscription
     *
     * @param string $id
     *
     * @return DataSift_Pylon
     */
    public function find($id)
    {
        return new self($this->_user, self::get($this->_user, $id));
    }

    /**
     * Class method to find all Subscriptions
     *
     *
     * @return DataSift_Pylon
     */
    public function findAll($page = 1, $per_page = 20, $order_by = self::ORDERBY_CREATED_AT, $order_dir = self::ORDERDIR_ASC)
    {

        $results = self::getAll($this->_user, $page, $per_page, $order_by, $order_dir);

        if (isset($results['subscriptions'])) { // Cope with pagination
            $results = $results['subscriptions'];
        }

        $retval = array();

        foreach ($results as $pylon) {
            $retval[] = new self($this->_user, $pylon);
        }

        return $retval;
    }

    /**
     * Get an existing recordings.
     *
     * @param Datasift_User $user The Datasift user object
     * @param string $id The id of the existing pylon
     *
     * @throws DataSift_Exception_InvalidData
     *
     * @return DataSift_Pylon
     */
    static public function get($user, $id = false)
    {
        $params = array();

        if ($id)  {
            $params['id'] = $id;
        }

        return $user->get('pylon/get', $params);
    }

    /**
     * List recordings
     *
     * @param Datasift_User $user The Datasift user object
     * @param int page The page of recordings to return
     * @param int per_page The number of recordings to display per page
     * @param string order_by The field to order the results by
     * @param string order_dir The direction to order the results by (ASC/DESC)
     *
     * @throws DataSift_Exception_InvalidData
     *
     * @return array
     */
    static public function getAll($user, $page = 1, $per_page = 20, $order_by = self::ORDERBY_CREATED_AT, $order_dir = self::ORDERDIR_ASC)
    {
        if ($page < 1) {
            throw new DataSift_Exception_InvalidData("The specified page number is invalid");
        }

        if ($per_page < 1) {
            throw new DataSift_Exception_InvalidData("The specified per_page value is invalid");
        }

        $params = array(
            'page' => $page,
            'per_page' => $per_page,
            'order_by' => $order_by,
            'order_dir' => $order_dir,
        );

        return $user->get('pylon/get', $params);
    }


    /**
     * Validate CSDL
     *
     * @param Datasift_User $user The Datasift user object
     * @param string $csdl The CSDL to validate
     *
     * @return array The response from validating the CSDL
     */
    static public function validate($user, $csdl)
    {
        return $user->post('pylon/validate', array('csdl' => $csdl));
    }

    /**
     * Load an existing pylon from hash
     *
     * Previously called fromHash
     *
     * @param Datasift_User $user The Datasift user object
     * @param string $hash The Hash of the recording
     *
     * @return DataSift_Pylon
     */
    static public function fromId($user, $id)
    {
        return new self($user, self::get($user, $id));
    }

    /**
     * Loads an pylon object from the get data
     *
     * @param array $data An array containing the subscription data.
     *
     * @throws DataSift_Exception_InvalidData
     */
    private function load($data)
    {
        if (empty($data)) 
        {
            throw new DataSift_Exception_InvalidData('No data found');
        }

        //Assign the instance variables
        foreach ($data as $key => $value)
        {
            $this->{'_'.$key} = $value;
        }
    }

    /**
     * Updates the object with fresh results from get
     *
     * @throws DataSift_Exception_InvalidData
     */
    public function reload()
    {
        if (strlen($this->_id) == 0) {
            throw new DataSift_Exception_InvalidData('Unable to reload pylon without an ID');
        }

        $this->load(self::get($this->_user, $this->_id));
    }

    /**
     * Sets the CSDL
     *
     * @param string $csdl the csdl string.
     *
     */
    public function setCsdl($csdl)
    {
        $this->_csdl = trim($csdl);
    }

    /**
     * Gets the CSDL
     *
     * @return string The CSDL assigned to the object
     */
    public function getCsdl()
    {
        return $this->_csdl;
    }

    /**
     * Sets the Name
     *
     * @param string $name The name of the pylon
     *
     */
    public function setName($name)
    {
        $this->_name = trim($name);
    }

    /**
     * Gets the name of this Subscription
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Gets the Hash
     *
     * @return string $hash The hash of the pylon recording
     */
    public function getHash()
    {
        return $this->_hash;
    }

    /**
     * Gets the ID
     *
     * @return string $id The ID of the pylon
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Gets the current volume of this PYLON subscription
     *
     * @return integer
     */
    public function getVolume()
    {
        return $this->_volume;
    }

    /**
     * Gets the status of this PYLON subscription
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Gets the id of the Identity this PYLON subscription is owned by
     *
     * @return string
     */
    public function getIdentityId()
    {
        return $this->_identity_id;
    }

    /**
     * Gets the start time of this PYLON subscription
     *
     * @return integer
     */
    public function getStart()
    {
        return $this->_start;
    }

    /**
     * Gets the end time of this PYLON subscription
     *
     * @return integer
     */
    public function getEnd()
    {
        return $this->_end;
    }

    /**
     * Gets the remaining capacity for this PYLON subscription
     *
     * @return integer
     */
    public function getRemainingIndexCapacity()
    {
        return $this->_remaining_index_capacity;
    }

    /**
     * Gets the remaining PYLON account capacity
     *
     * @return integer
     */
    public function getRemainingAccountCapacity()
    {
        return $this->_remaining_account_capacity;
    }

    /**
     * Has this Subscription reached its capacity?
     *
     * @return boolean
     */
    public function hasReachedCapacity()
    {
        return $this->_reached_capacity;
    }

    /**
     * Compiles the CSDL of this object
     *
     * @param string $csdl If a CSDL string is passed to compile it will set the CSDL for the object
     *
     * @return array Response from the compile
     *
     */
    public function compile($csdl = false)
    {
        if ($csdl) {
            $this->_csdl = $csdl;
        }

        if (strlen($this->_csdl) == 0) {
            throw new DataSift_Exception_InvalidData('Cannot compile an empty definition.');
        }

        $res = $this->_user->post('pylon/compile', array('csdl' => $this->_csdl));

        $this->_hash = $res['hash'];

        return $res;

    }

    /**
     * Creates a new recording or restarts an existing one if an ID is present
     *
     * @param string $hash If hash is provided it will be set
     * @param string $name If name is provided it will be set
     *
     */
    public function start($hash = false, $name = false) 
    {
        if ($hash) {
            $this->_hash = $hash;
        }

        if ($name) {
            $this->_name = $name;
        }

        if (!empty($this->_id)) {
            $this->restart();
        }
        else {
            $this->create();
        }
    }

    public function create($hash = false, $name = false)
    {
        if ($hash) {
            $this->_hash = $hash;
        }

        if ($name) {
            $this->_name = $name;
        }

        if (strlen($this->_hash) == 0) {
            throw new DataSift_Exception_InvalidData('Cannot start a recording without a hash');
        }

        $params = array('hash' => $this->_hash);

        if (!empty($this->_name))
        {
            $params['name'] = $this->_name;
        }

        $response = $this->_user->post('pylon/start', $params);

        $this->load($response);

        return $response;
    }

    /**
     * Restarts the pylon recording
     *
     * @param string $id If ID is provided it will be set
     *
     */
    public function restart($id = false)
    {
        if ($id) {
            $this->_id = $id;
        }

        $this->_user->put('pylon/start', array('id' => $this->_id));
    }

    /**
     * Stops the pylon recording
     *
     * @param string $id If ID is provided it will be set
     */
    public function stop($id = false)
    {
        if ($id) {
            $this->_id = $id;
        }
        if (strlen($this->_id) == 0) {
            throw new DataSift_Exception_InvalidData('Unable to reload pylon without an ID');
        }

        $this->_user->post('pylon/stop', array('id' => $this->_id));
    }


    /**
     * Analyze the recording
     *
     * @param array $parameters the parameter array to be used to analyze the data set
     * @param string $filter additional CSDL filter
     * @param int $start the start time of the pylon
     * @param int $end the end time of the pylon
     * @param string $id If id is provided it will be set
     *
     * @return array Response from the compile
     */
    public function analyze($parameters, $filter = false, $start = false, $end = false, $id = false)
    {
        if ($id) {
            $this->_id = $id;
        }

        //If parameters is not an array try and decode it
        if (!is_array($parameters)) {
            $parameters = json_decode($parameters);
        }

        if (empty($parameters)) {
            throw new DataSift_Exception_InvalidData('Parameters must be supplied as an array or valid JSON');
        }
        $params = array(
            'id'       =>    $this->_id,
            'parameters' =>    $parameters
        );

        //Set optional request parameters
        if ($filter) {
            $params['filter'] = $filter;
        }

        if ($start) {
            $params['start'] = $start;
        }

        if ($end) {
            $params['end'] = $end;
        }

        return $this->_user->post('pylon/analyze', $params);
    }

    /**
     * Analyze the tags in the data set
     *
     * @param string $id If ID is provided it will be set
     *
     * @return array Response from the tags endpoint
     */
    public function tags($id = false)
    {
        if ($id) {
            $this->_id = $id;
        }
        if (strlen($this->_id) == 0) {
            throw new DataSift_Exception_InvalidData('Unable to get tags without an ID');
        }

        return $this->_user->get('pylon/tags', array('id' => $this->_id));
    }

    /**
     * Returns a list of sample interactions (super-public)
     *
     * @param Datasift_User $user The Datasift user object
     * @param string $id The id of the existing pylon
     * @param string $filter additional CSDL filter
     * @param int $start the start time of the pylon
     * @param int $end the end time of the pylon
     * @param int $count optional value to set the count
     *
     * @throws DataSift_Exception_InvalidData
     *
     * @return array Response from the sample endpoint
     */
    public function sample($filter = false, $start = false, $end = false, $count = false, $id = false)
    {
        if ($id) {
            $this->_id = $id;
        }

        if (strlen($this->_id) == 0) {
            throw new DataSift_Exception_InvalidData('Unable to retrieve pylon sample without an ID');
        }

        $params = array('id' => $this->_id);

        if ($start) {
            $params['start'] = $start;
        }

        if ($end) {
            $params['end'] = $end;
        }

        if ($count) {
            $params['count'] = $count;
        }

        if ($filter) {
            $params['filter'] = $filter;
            return $this->_user->post('pylon/sample', $params);
        } 
        else {
            return $this->_user->get('pylon/sample', $params);
        }
    }

    /**
     * Updates a recording with a new hash and or name
     *
     * @param string $id The id of the existing recording
     * @param string $hash The new hash of the pylon recording
     * @param string $name The new updated name of the recording
     *
     * @throws DataSift_Exception_InvalidData
     */
    public function update($id = false, $hash = false, $name = false)
    {

        if ($id) {
            $this->_id = $id;
        }
        if ($hash) {
            $this->_hash = $hash;
        }
        if ($name) {
            $this->_name = $name;            
        }

        $params = array(
            'id'    => $this->_id, 
            'hash'  => $this->_hash, 
            'name'  => $this->_name
        );

        $this->_user->put('pylon/update', $params);
    }

}
