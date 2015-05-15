<?php
/**
 * DataSift client
 *
 * This software is the intellectual property of MediaSift Ltd., and is covered
 * by retained intellectual property rights, including copyright.
 * Distribution of this software is strictly forbidentityden under the terms of this license.
 *
 * @category  DataSift
 * @package   PHP-client
 * @author    Stuart Dallas <stuart@3ft9.com>
 * @copyright 2011 MediaSift Ltd.
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      http://www.mediasift.com
 */

/**
 * The DataSift_Account class defines base methods used by the identites endpoints.
 *
 * @category DataSift
 * @package  PHP-client
 * @author   Chris Knight <chris.knight@datasift.com>
 * @license  http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link     http://www.mediasift.com
 */
class DataSift_Account
{
    /**
     * apiClient
     * 
     * @var string 
     */
    protected $apiClient = 'DataSift_ApiClient';
    
    /**
     * User
     * 
     * @var DataSift_User 
     */
    protected $user = null;
    
    /**
     * Constructor
     * 
     * @param DataSift_User $user
     */
    public function __construct(DataSift_User $user) 
    {
        $this->user = $user;
    }

    /**
     * Set the class to use when calling the API
     *
     * @param string $apiClient The class to use.
     *
     * @return void
     * @throws DataSift_Exception_InvalidData
     */
    public function setApiClient($apiClient)
    {
        if (!class_exists($apiClient) or !method_exists($apiClient, 'call')) {
            throw new DataSift_Exception_InvalidData('Class "'.$apiClient.'" does not exist');
        }
        $this->apiClient = $apiClient;
    }
    
    /**
     * Central call handling
     * 
     * @param string    $method
     * @param string    $endPoint
     * @param array     $params
     * 
     * @return type
     */
    protected function call($method, $endPoint, $successCode, $params = array(), $qs = array())
    {
        $res = call_user_func(
            array($this->apiClient, $method),
            $this->user,
            $endPoint,
            $params,
            $this->getUserAgent(),
            $successCode,
            $qs
        );
        
        return $res;
    }
    
    /**
     * Process the response
     * 
     * @param array $response
     * @param array $successCode
     */
    protected function processResponse($response, $successCode) 
    {
        if (in_array($response['response_code'], $successCode)) {
            if(count($response['data']) > 0) {
                return $response['data'];
            }
            
            return true;
        }

        return array(
            'response_code' => $response['response_code'],
            'error' => $response['data']['error']
        );
    }
    
    /**
     * Returns the user agent this library should use for all API calls.
     *
     * @return string
     */
    protected function getUserAgent()
    {
        return DataSift_User::USER_AGENT;
    }
}