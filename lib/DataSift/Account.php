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
    protected $_user = null;

    /**
     * Constructor
     *
     * @param DataSift_User $user
     */
    public function __construct(DataSift_User $user)
    {
        $this->_user = $user;
    }

    /**
     * Returns the user agent this library should use for all API calls.
     *
     * @return string
     */
    public function usage($start = false, $end = false, $period = null)
    {
        $params = array();

        if ($start) {
            $params['start'] = $start;
        }
        if ($end) {
            $params['end'] = $end;
        }
        if (isset($period)) {
            $params['period'] = $period;
        }

        return $this->_user->get('account/usage', $params);
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
