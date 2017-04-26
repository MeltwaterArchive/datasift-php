<?php

namespace DataSift\Tests;

use DataSift_User;
use Exception;

class MockApiClient
{
    /**
     * @var string $_response the API response
     */
    private static $_response = false;

    /**
     * Set the response object
     *
     * @param object $r Response
     *
     * @return void
     */
    public static function setResponse($r)
    {
        self::$_response = $r;
    }

    /**
     * Set the response object
     *
     * @param string $username   Username
     * @param string $api_key    API key
     * @param string $endpoint   URL
     * @param array  $params     URL parameters
     * @param string $user_agent User Agent string
     *
     * @return void
     */
    public static function call($username, $api_key, $endpoint, $params = array(), $user_agent = 'DataSiftPHP/0.0')
    {
        if (self::$_response === false) {
            throw new Exception('Expected response not set in mock object');
        }
        return self::$_response;
    }

    public static function get(
        DataSift_User $user,
        $endpoint,
        $params = array(),
        $userAgent = DataSift_User::USER_AGENT,
        $successCode = 200
    ) {
        return self::call($user, $endpoint, $params, $userAgent);
    }

    public static function post(
        DataSift_User $user,
        $endpoint,
        $params,
        $userAgent = DataSift_User::USER_AGENT,
        $successCode = 200
    ) {
        return self::call($user, $endpoint, $params, $userAgent);
    }

    public static function put(
        DataSift_User $user,
        $endpoint,
        $params,
        $userAgent = DataSift_User::USER_AGENT,
        $successCode = 200
    ) {
        return self::call($user, $endpoint, $params, $userAgent);
    }

    public static function delete(
        DataSift_User $user,
        $endpoint,
        $params = array(),
        $userAgent = DataSift_User::USER_AGENT,
        $successCode = 200
    ) {
        return self::call($user, $endpoint, $params, $userAgent);
    }
}
