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
class DataSift_Account_Identity extends DataSift_Account
{
    /**
     * Returns an identity
     * 
     * @param string $identity
     * 
     * @return mixed
     */
    public function get($identity)
    {
        $successCode = array(DataSift_ApiClient::HTTP_OK);
        
        $response = $this->call('get', 'account/identity/' . $identity, $successCode);
        return $this->processResponse($response, $successCode);
    }

    /**
     * Gets all the identities
     * 
     * @param string    $label
     * @param integer   $page
     * @param integer   $perPage
     * @return mixed
     */
    public function getAll($label = null, $page = 1, $perPage = 25)
    {
        $qs = array(
            'page' => $page,
            'per_page' => $perPage
        );

        $successCode = array(DataSift_ApiClient::HTTP_OK);
        
        $response = $this->call('get', 'account/identity', $successCode, array(), $qs);
        return $this->processResponse($response, $successCode);
    }
    
    /**
     * Creates an identity
     * 
     * @param string    $label
     * @param string    $master
     * @param string    $status
     * 
     * @return mixed
     */
    public function create($label, $master = false, $status = 'active')
    {
        $successCode = array(DataSift_ApiClient::HTTP_CREATED);
        
        $params = array(
            'label'     => $label,
            'master'    => $master,
            'status'    => $status
        );
        
        $response = $this->call('post', 'account/identity', $successCode, $params);
        return $this->processResponse($response, $successCode);
    }
    
    /**
     * Updates an identity
     * 
     * @param string    $identity
     * @param string    $master
     * @param string    $label
     * @param string    $status
     * 
     * @return mixed
     */
    public function update($identity, $label = null, $master = null, $status = null) 
    {
        $successCode = array(DataSift_ApiClient::HTTP_OK);
        
        $params = array(
            'label'     => $label,
            'master'    => $master,
            'status'    => $status
        );
        
        foreach ($params as $k => $v) {
            if ($v == null) {
                unset($params[$k]);
            }
        }
        
        $response = $this->call('put', 'account/identity/' . $identity, $successCode, $params);
        return $this->processResponse($response, $successCode);
    }
    
    /**
     * Deletes an identity
     * 
     * @param string    $identity
     * 
     * @return mixed
     */
    public function delete($identity)
    {
        $successCode = array(DataSift_ApiClient::HTTP_NO_CONTENT);
        
        $response = $this->call('delete', 'account/identity/' . $identity, $successCode);
        return $this->processResponse($response, $successCode);
    }
}
