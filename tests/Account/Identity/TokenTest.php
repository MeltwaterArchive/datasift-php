<?php

namespace DataSift\Tests;

use DataSift_Account_Identity_Token;
use DataSift_User;

class TokenTest extends \PHPUnit_Framework_TestCase
{
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_NO_CONTENT = 204;
    const HTTP_NOT_FOUND = 404;
    const HTTP_CONFLICT = 409;
    const HTTP_GONE = 410;

    /**
     * User
     *
     * @var DataSift_User
     */
    protected $user = false;

    /**
     * Setup
     */
    protected function setUp()
    {
        require_once(dirname(__FILE__) . '/../../../lib/datasift.php');
        require_once(dirname(__FILE__) . '/../../../config.php');

        $this->_user = new DataSift_User(USERNAME, API_KEY);
        $this->_user->setApiClient('\DataSift\Tests\MockApiClient');

        MockApiClient::setResponse(false);
    }

    /**
     * Data provider for testGetAllTokens
     */
    public function getAllTokensProvider()
    {
        return array(
            array(
                'identity_id' => 5912,
                'page' => 1,
                'per_page' => 10,
                'api_result' => array(
                    'response_code' => self::HTTP_OK,
                    'data' => array(
                        'count' => 2,
                        'tokens' => array(
                            array(
                                'service' => 'test',
                                'token' => md5('token'),
                                'created_at' => 1429002245,
                                'updated_at' => 1429002245,
                                'expires_at' => 1430000000,
                            ),
                            array(
                                'service' => 'test2',
                                'token' => md5('token2'),
                                'created_at' => 1429002245,
                                'updated_at' => 1429002245,
                                'expires_at' => 1430000000,
                            )
                        )
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'count' => 2,
                    'tokens' => array(
                        array(
                            'service' => 'test',
                            'token' => md5('token'),
                            'created_at' => 1429002245,
                            'updated_at' => 1429002245,
                            'expires_at' => 1430000000,
                        ),
                        array(
                            'service' => 'test2',
                            'token' => md5('token2'),
                            'created_at' => 1429002245,
                            'updated_at' => 1429002245,
                            'expires_at' => 1430000000,
                        )
                    )
                ),
                array(
                    'identity_id' => 5912,
                    'page' => 1,
                    'per_page' => 10,
                    'api_result' => array(
                        'response_code' => self::HTTP_NOT_FOUND,
                        'data' => array(
                            'error' => 'An Identity with the supplied id was not found'
                        ),
                        'rate_limit' => 200,
                        'rate_limit_remaining' => 150,
                    ),
                    'expected_result' => array(
                        'response_code' => self::HTTP_NOT_FOUND,
                        'error' => 'An Identity with the supplied id was not found'
                    )
                ),
                array(
                    'identity_id' => 5912,
                    'page' => 1,
                    'per_page' => 10,
                    'api_result' => array(
                        'response_code' => self::HTTP_GONE,
                        'data' => array(
                            'error' => 'The Identity with the supplied id has been deleted'
                        ),
                        'rate_limit' => 200,
                        'rate_limit_remaining' => 150,
                    ),
                    'expected_result' => array(
                        'response_code' => self::HTTP_GONE,
                        'error' => 'The Identity with the supplied id has been deleted'
                    )
                )
            ),
        );
    }

    /**
     * @dataProvider getAllTokensProvider
     * @covers       Datasift_Account_Identity_Token::getAll
     */
    public function testGetAllTokens($identityId, $page, $perPage, $apiResult, $expectedResult)
    {
        $identityToken = new DataSift_Account_Identity_Token($this->_user);

        MockApiClient::setResponse($apiResult);

        if (isset($expectedResult['error'])) {
            $this->setExpectedException('DataSift_Exception_APIError');
        }

        $result = $identityToken->getAll($identityId, $page, $perPage);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testGetToken
     */
    public function getTokenProvider()
    {
        return array(
            array(
                'identity_id' => 5912,
                'service' => 'test',
                'api_result' => array(
                    'response_code' => self::HTTP_OK,
                    'data' => array(
                        'service' => 'test',
                        'token' => md5('token'),
                        'created_at' => 1429002245,
                        'updated_at' => 1429002245,
                        'expires_at' => 1430000000,
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,

                ),
                'expected_result' => array(
                    'service' => 'test',
                    'token' => md5('token'),
                    'created_at' => 1429002245,
                    'updated_at' => 1429002245,
                    'expires_at' => 1430000000,
                )
            ),
            array(
                'identity_id' => 5912,
                'service' => 'test',
                'api_result' => array(
                    'response_code' => self::HTTP_NOT_FOUND,
                    'data' => array(
                        'error' => 'An Identity with the supplied id was not found'
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'response_code' => self::HTTP_NOT_FOUND,
                    'error' => 'An Identity with the supplied id was not found'
                )
            ),
            array(
                'identity_id' => 5912,
                'service' => 'test',
                'api_result' => array(
                    'response_code' => self::HTTP_GONE,
                    'data' => array(
                        'error' => 'The Identity with the supplied id has been deleted'
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'response_code' => self::HTTP_GONE,
                    'error' => 'The Identity with the supplied id has been deleted'
                )
            )
        );
    }

    /**
     * @dataProvider getTokenProvider
     * @covers       Datasift_Account_Identity_Token::get
     */
    public function testGetToken($identityId, $service, $apiResult, $expectedResult)
    {
        $identityToken = new DataSift_Account_Identity_Token($this->_user);

        MockApiClient::setResponse($apiResult);

        if (isset($expectedResult['error'])) {
            $this->setExpectedException('DataSift_Exception_APIError');
        }

        $result = $identityToken->get($identityId, $service);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testCreateToken
     */
    public function createTokenProvider()
    {
        return array(
            array(
                'identity_id' => 5912,
                'service' => 'test',
                'token' => md5('test'),
                'api_result' => array(
                    'response_code' => self::HTTP_CREATED,
                    'data' => array(
                        'service' => 'test',
                        'token' => md5('test'),
                        'created_at' => 1429002245,
                        'updated_at' => 1429002245,
                        'expires_at' => 1430000000,
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'service' => 'test',
                    'token' => md5('test'),
                    'created_at' => 1429002245,
                    'updated_at' => 1429002245,
                    'expires_at' => 1430000000,
                )
            ),
            array(
                'identity_id' => 5912,
                'service' => 'test',
                'token' => md5('test'),
                'api_result' => array(
                    'response_code' => self::HTTP_NOT_FOUND,
                    'data' => array(
                        'error' => 'An Identity with the supplied id was not found'
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'response_code' => self::HTTP_NOT_FOUND,
                    'error' => 'An Identity with the supplied id was not found'
                )
            ),
            array(
                'identity_id' => 5912,
                'service' => 'test',
                'token' => md5('test'),
                'api_result' => array(
                    'response_code' => self::HTTP_CONFLICT,
                    'data' => array(
                        'error' => 'A token for test already exists for that Identity'
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'response_code' => self::HTTP_CONFLICT,
                    'error' => 'A token for test already exists for that Identity'
                )
            ),
            array(
                'identity_id' => 5912,
                'service' => 'test',
                'token' => md5('test'),
                'api_result' => array(
                    'response_code' => self::HTTP_GONE,
                    'data' => array(
                        'error' => 'The Identity with the supplied id has been deleted'
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'response_code' => self::HTTP_GONE,
                    'error' => 'The Identity with the supplied id has been deleted'
                )
            )
        );
    }

    /**
     * @dataProvider createTokenProvider
     * @covers       Datasift_Account_Identity_Token::create
     */
    public function testCreateToken($identityId, $service, $token, $apiResult, $expectedResult)
    {
        $identityToken = new DataSift_Account_Identity_Token($this->_user);

        MockApiClient::setResponse($apiResult);

        if (isset($expectedResult['error'])) {
            $this->setExpectedException('DataSift_Exception_APIError');
        }

        $result = $identityToken->create($identityId, $service, $token);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testUpdateToken
     */
    public function updateTokenProvider()
    {
        return array(
            array(
                'identity_id' => 5912,
                'service' => 'test',
                'token' => md5('test'),
                'api_result' => array(
                    'response_code' => self::HTTP_CREATED,
                    'data' => array(
                        'service' => 'test',
                        'token' => md5('test'),
                        'created_at' => 1429002245,
                        'updated_at' => 1429002245,
                        'expires_at' => 1430000000,
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'service' => 'test',
                    'token' => md5('test'),
                    'created_at' => 1429002245,
                    'updated_at' => 1429002245,
                    'expires_at' => 1430000000,
                )
            ),
            array(
                'identity_id' => 5912,
                'service' => 'test',
                'token' => md5('test'),
                'api_result' => array(
                    'response_code' => self::HTTP_OK,
                    'data' => array(
                        'service' => 'test',
                        'token' => md5('test'),
                        'created_at' => 1429002245,
                        'updated_at' => 1429002245,
                        'expires_at' => 1430000000,
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'service' => 'test',
                    'token' => md5('test'),
                    'created_at' => 1429002245,
                    'updated_at' => 1429002245,
                    'expires_at' => 1430000000,
                )
            ),
            array(
                'identity_id' => 5912,
                'service' => 'test',
                'token' => md5('test'),
                'api_result' => array(
                    'response_code' => self::HTTP_NOT_FOUND,
                    'data' => array(
                        'error' => 'An Identity with the supplied id was not found'
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'response_code' => self::HTTP_NOT_FOUND,
                    'error' => 'An Identity with the supplied id was not found'
                )
            ),
            array(
                'identity_id' => 5912,
                'service' => 'test',
                'token' => md5('test'),
                'api_result' => array(
                    'response_code' => self::HTTP_GONE,
                    'data' => array(
                        'error' => 'The Identity with the supplied id has been deleted'
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'response_code' => self::HTTP_GONE,
                    'error' => 'The Identity with the supplied id has been deleted'
                )
            )
        );
    }

    /**
     * @dataProvider updateTokenProvider
     * @covers       Datasift_Account_Identity_Token::update
     */
    public function testUpdateToken($identityId, $service, $token, $apiResult, $expectedResult)
    {
        $identityToken = new DataSift_Account_Identity_Token($this->_user);

        MockApiClient::setResponse($apiResult);

        if (isset($expectedResult['error'])) {
            $this->setExpectedException('DataSift_Exception_APIError');
        }

        $result = $identityToken->update($identityId, $service, $token);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testDeleteToken
     */
    public function deleteTokenProvider()
    {
        return array(
            array(
                'identity_id' => 5912,
                'service' => 'test',
                'api_result' => array(
                    'response_code' => self::HTTP_NO_CONTENT,
                    'data' => array(),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array()
            ),
            array(
                'identity_id' => 5912,
                'service' => 'test',
                'api_result' => array(
                    'response_code' => self::HTTP_NOT_FOUND,
                    'data' => array(
                        'error' => 'An Identity with the supplied id was not found'
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'response_code' => self::HTTP_NOT_FOUND,
                    'error' => 'An Identity with the supplied id was not found'
                )
            ),
            array(
                'identity_id' => 5912,
                'service' => 'test',
                'api_result' => array(
                    'response_code' => self::HTTP_NOT_FOUND,
                    'data' => array(
                        'error' => 'A token for test was not found for the Identity'
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'response_code' => self::HTTP_NOT_FOUND,
                    'error' => 'A token for test was not found for the Identity'
                )
            ),
            array(
                'identity_id' => 5912,
                'service' => 'test',
                'api_result' => array(
                    'response_code' => self::HTTP_GONE,
                    'data' => array(
                        'error' => 'The Identity with the supplied id has been deleted'
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'response_code' => self::HTTP_GONE,
                    'error' => 'The Identity with the supplied id has been deleted'
                )
            ),
            array(
                'identity_id' => 5912,
                'service' => 'test',
                'api_result' => array(
                    'response_code' => self::HTTP_GONE,
                    'data' => array(
                        'error' => "The Identity's token for <service> has been deleted"
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'response_code' => self::HTTP_GONE,
                    'error' => "The Identity's token for <service> has been deleted"
                )
            )
        );
    }

    /**
     * @dataProvider deleteTokenProvider
     * @covers       Datasift_Account_Identity_Token::delete
     */
    public function testDeleteToken($identityId, $service, $apiResult, $expectedResult)
    {
        $identityToken = new DataSift_Account_Identity_Token($this->_user);

        MockApiClient::setResponse($apiResult);

        if (isset($expectedResult['error'])) {
            $this->setExpectedException('DataSift_Exception_APIError');
        }

        $result = $identityToken->delete($identityId, $service);

        $this->assertEquals($expectedResult, $result);
    }
}
