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
        return $this->_user->get('account/identity/'. $identity);
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
        $params = array(
            'page' => $page,
            'per_page' => $perPage
        );

        if ($label !== null) {
            $params['label'] = $label;
        }

        return $this->_user->get('account/identity', $params);
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

        $params = array(
            'label'     => $label,
            'master'    => $master,
            'status'    => $status
        );

        return  $this->_user->post('account/identity', $params);
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

        $params = array();
        if (!is_null($label)) {
            $params['label'] = $label;
        }
        if (!is_null($master)) {
            $params['master'] = $master;
        }
        if (!is_null($status)) {
            $params['status'] = $status;
        }

        return $this->_user->put('account/identity/' . $identity, $params);
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
       return $response = $this->_user->delete('account/identity/' . $identity);
    }
}
