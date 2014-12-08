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
 * The DataSift_Account_Identity class defines identity entities endpoint.
 *
 * @category DataSift
 * @package  PHP-client
 * @author   Chris Knight <chris.knight@datasift.com>
 * @license  http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link     http://www.mediasift.com
 */
class DataSift_Account_Identity_Limit extends DataSift_Account
{    
    /**
     * Returns the limit for a service
     * 
     * @param string    $identity
     * @param string    $service
     * 
     * @return mixed
     */
    public function get($identity, $service)
    {
        $successCode = array(DataSift_ApiClient::HTTP_OK);
        
        $response = $this->call('get', 'account/identity/' . $identity . '/limit/' . $service, $successCode);
        return $this->processResponse($response, $successCode);
    }
    
    /**
     * Get all the limits for a service
     * 
     * @param string    $service
     * @param integer   $page
     * @param integer   $per_page
     * 
     * @return mixed
     */
    public function getAll($service, $page = 1, $perPage = 25)
    {
        $qs = array(
            'page' => $page,
            'per_page' => $perPage
        );

        $successCode = array(DataSift_ApiClient::HTTP_OK);
        
        $response = $this->call('get', 'account/identity/limit/' . $service, $successCode, array(), $qs);
        return $this->processResponse($response, $successCode);
    }
    
    /**
     * Create a limit for a service
     * 
     * @param string    $identity
     * @param string    $service
     * @param integer   $limit
     * 
     * @return mixed
     */
    public function create($identity, $service, $total_allowance)
    {
        $successCode = array(DataSift_ApiClient::HTTP_CREATED);
        
        $params = array(
            'service'           => $service,
            'total_allowance'   => $total_allowance
        );
        
        $response = $this->call('post', 'account/identity/' . $identity . '/limit', $successCode, $params);
        return $this->processResponse($response, $successCode);
    }
    
    /**
     * Update the limit for an service
     * 
     * @param string    $identity
     * @param integer   $limit
     * 
     * @return mixed
     */
    public function update($identity, $service, $limit)
    {
        $successCode = array(DataSift_ApiClient::HTTP_OK);
        
        $params = array(
            'total_allowance'   => $limit
        );
        
        $response = $this->call('put', 'account/identity/' . $identity . '/limit/' . $service, $successCode, $params);
        return $this->processResponse($response, $successCode);
    }
    
    /**
     * Delete the limit for an service
     * 
     * @param string    $identity
     * @return mixed
     */
    public function delete($identity, $service)
    {
        $successCode = array(DataSift_ApiClient::HTTP_NO_CONTENT);
        
        $response = $this->call('delete', 'account/identity/' . $identity . '/limit/' . $service, $successCode);
        return $this->processResponse($response, $successCode);
    }
}
