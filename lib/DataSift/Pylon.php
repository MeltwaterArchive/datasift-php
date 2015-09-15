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
     * Get an existing recordings.
     *
     * @param Datasift_User $user The Datasift user object
     * @param string $hash The hash of the existing pylon
     *
     * @throws DataSift_Exception_InvalidData
     *
     * @return array of existing recordings
     */
    static public function get($user, $hash=false)
    {    
        $params = array();
        
        if ($hash)  {
            $params['hash'] = $hash;
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
     * @return array of existing recordings
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

        $res = $user->get('pylon/get', $params);

        $retval = array(
            'subscriptions' => array()
        );
        
        foreach ($res['subscriptions'] as $pylon) {
            $analysis = new DataSift_Pylon($user, $pylon);
            $retval['subscriptions'][] = $analysis;
        }
        
        return $retval;
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
     * @param Datasift_User $user The Datasift user object
     * @param string $hash The Hash of the recording
     *
     * @return DataSift_Pylon
     */
    static public function fromHash($user, $hash)
    {    
        return new self($user, self::get($user, $hash));
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
        if (empty($data)) {
            throw new DataSift_Exception_InvalidData('No data found');
        }

        //Assign the instance variables
        foreach ($data as $key => $value) {
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
        if (strlen($this->_hash) == 0) {
            throw new DataSift_Exception_InvalidData('Unable to reload pylon without a hash');
        }

        $this->load(self::get($this->_user, $this->_hash));
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
     * Gets the Hash
     *
     * @return string $name The name of the pylon
     */
    public function getHash()
    {
        return $this->_hash;
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
     * Starts the pylon
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
        
        if (strlen($this->_hash) == 0) {
            throw new DataSift_Exception_InvalidData('Cannot start a recording without a hash');
        }

        $params = array('hash' => $this->_hash);
        
        if (!empty($this->_name)) 
        {
            $params['name'] = $this->_name;
        }

        $this->_user->post('pylon/start', $params);
    }

    /**
     * Stops the pylon
     *
     * @param string $hash If hash is provided it will be set
     */
    public function stop($hash = false)
    {    
        if ($hash) {
            $this->_hash = $hash;
        }
        
        if (strlen($this->_hash) == 0) {
            throw new DataSift_Exception_InvalidData('Cannot stop a recording without a hash');
        }
        
        $this->_user->post('pylon/stop', array('hash' => $this->_hash));
    }


    /**
     * Analyze the recording
     *
     * @param array $parameters the parameter array to be used to analyze the data set
     * @param string $filter additional CSDL filter
     * @param int $start the start time of the pylon
     * @param int $end the end time of the pylon
     * @param string $hash If hash is provided it will be set
     *
     * @return array Response from the compile
     */
    public function analyze($parameters, $filter = false, $start = false, $end = false, $hash = false)
    {    
        if ($hash) {
            $this->_hash = $hash;
        }
        
        //If parameters is not an array try and decode it
        if (!is_array($parameters)) {
            $parameters = json_decode($parameters);
        }
        
        if (empty($parameters)) {
            throw new DataSift_Exception_InvalidData('Parameters must be supplied as an array or valid JSON');
        }
        $params = array(
            'hash'       =>    $this->_hash,
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
     * @param string $hash If hash is provided it will be set
     *
     * @return array Response from the tags endpoint
     */
    public function tags($hash=false)
    {    
        if ($hash) {
            $this->_hash = $hash;
        }
        
        if (strlen($this->_hash) == 0) {
            throw new DataSift_Exception_InvalidData('Unable to analyze tags without a hash');
        }
        
        return $this->_user->get('pylon/tags', array('hash' => $this->_hash));
    }

}