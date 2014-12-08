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
 * The DataSift_Account_Identity_Token class defines identity tokens endpoint.
 *
 * @category DataSift
 * @package  PHP-client
 * @author   Chris Knight <chris.knight@datasift.com>
 * @license  http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link     http://www.mediasift.com
 */
class DataSift_Account_Identity_Token extends DataSift_Account
{
    /**
     * Gets the token for a service
     * 
     * @param string    $identity
     * @param string    $service
     * 
     * @return mixed
     */
    public function get($identity, $service) 
    {
        $successCode = array(DataSift_ApiClient::HTTP_OK);
        
        $response = $this->call('get', 'account/identity/' . $identity . '/token/' . $service, $successCode);
        return $this->processResponse($response, $successCode);
    }
    
    /**
     * Get all the tokens for an identity
     * 
     * @param string    $identity
     * @param integer   $page
     * @param integer   $perPage
     * 
     * @return mixed
     */
    public function getAll($identity, $page = 1, $perPage = 25)
    {
        $qs = array(
            'page' => $page,
            'per_page' => $perPage
        );
        $successCode = array(DataSift_ApiClient::HTTP_OK);
        
        $response = $this->call('get', 'account/identity/' . $identity . '/token', $successCode, array(), $qs);
        return $this->processResponse($response, $successCode);
    }
    
    /**
     * Creates a token for a service
     * 
     * @param string    $identity
     * @param string    $service
     * @param string    $token
     * @param string    $expiresAt
     * 
     * @return mixed
     */
    public function create($identity, $service, $token) 
    {
        $successCode = array(DataSift_ApiClient::HTTP_CREATED);
        
        $params = array(
            'service'       => $service,
            'token'         => $token,
        );
        
        $response = $this->call('post', 'account/identity/' . $identity . '/token', $successCode, $params);
        return $this->processResponse($response, $successCode);
    }
    
    /**
     * Updates the token for a service
     * 
     * @param string    $identity
     * @param string    $service
     * @param string    $expiresAt
     * 
     * @return mixed
     */
    public function update($identity, $service, $token)
    {
        $successCode = array(DataSift_ApiClient::HTTP_CREATED, DataSift_ApiClient::HTTP_OK);
        
        $params = array(
            'token' => $token,
        );
        
        $response = $this->call('put', 'account/identity/' . $identity . '/token/' . $service, $successCode, $params);
        return $this->processResponse($response, $successCode);
    }
    
    /**
     * Deletes a token for a service
     * 
     * @param string    $identity
     * @param string    $service
     * 
     * @return mixed
     */
    public function delete($identity, $service) 
    {
        $successCode = array(DataSift_ApiClient::HTTP_NO_CONTENT);
        
        $response = $this->call('delete', 'account/identity/' . $identity . '/token/' . $service, $successCode);
        return $this->processResponse($response, $successCode);
    }
}
