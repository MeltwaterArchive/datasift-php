<?php
/**
 * DataSift client
 *
 * This software is the intellectual property of MediaSift Ltd., and is covered
 * by retained intellectual property rights, including copyright.
 * Distribution of this software is strictly forbidden under the terms of this license.
 *
 * The DataSift_Account_Identity
 *
 * @category  DataSift
 * @package   PHP-client
 * @author    Chris Knight <chris.knight@datasift.com>
 * @copyright 2013 MediaSift Ltd.
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      http://www.mediasift.com
 */
class IdentityTest extends PHPUnit_Framework_TestCase
{
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_NO_CONTENT = 204;
    const HTTP_NOT_FOUND = 404;
    const HTTP_CONFLICT = 409;
    const HTTP_GONE = 410;
    
    /**
     * User
     * @var DataSift_User
     */
    protected $user = false;

    /**
     * Setup
     */
    protected function setUp()
    {
        require_once(dirname(__FILE__).'/../lib/datasift.php');
        require_once(dirname(__FILE__).'/../config.php');

        $this->_user = new DataSift_User(USERNAME, API_KEY);
        $this->_user->setApiClient('DataSift_MockApiClient');

        DataSift_MockApiClient::setResponse(false);
    }

    /**
     * Data provider for testGetAll
     */
    public function getAllProvider() 
    {
        return array(
            array(
                'label' => null,
                'api_result' => array(
                    'response_code' => self::HTTP_OK,
                    'data' => array(
                        'count' => 1,
                        'identities' => array(
                            array(
                                'id' => 5912,
                                'api_key' => md5('test'),
                                'label' => null,
                                'status' => 'active',
                                'master' => true,
                                'created_at' => 1429002245,
                                'updated_at' => 1429002245,
                                'expires_at' => 1430000000,
                            ),
                        ),
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'count' => 1,
                    'identities' => array(
                        array(
                            'id' => 5912,
                            'api_key' => md5('test'),
                            'label' => null,
                            'status' => 'active',
                            'master' => true,
                            'created_at' => 1429002245,
                            'updated_at' => 1429002245,
                            'expires_at' => 1430000000,
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * @dataProvider getAllProvider
     * @covers Datasift_Account_Identity::getAll
     */
    public function testGetAll($label, $apiResult, $expectedResult)
    {
        $page = 1;
        $perPage = 10;

        $identity = new DataSift_Account_Identity($this->_user);

        DataSift_MockApiClient::setResponse($apiResult);

        if (isset($expectedResult['error'])) {
            $this->setExpectedException('DataSift_Exception_APIError');
        }
        
        $result = $identity->getAll($label, $page, $perPage);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testGet
     */
    public function getProvider() 
    {
        return array(
            array(
                'identity_id' => 5912,
                'api_result' => array(
                    'response_code' => self::HTTP_OK,
                    'data' => array(
                        'id' => 5912,
                        'api_key' => md5('test'),
                        'label' => null,
                        'status' => 'active',
                        'master' => true,
                        'created_at' => 1429002245,
                        'updated_at' => 1429002245,
                        'expires_at' => 1430000000,
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'id' => 5912,
                    'api_key' => md5('test'),
                    'label' => null,
                    'status' => 'active',
                    'master' => true,
                    'created_at' => 1429002245,
                    'updated_at' => 1429002245,
                    'expires_at' => 1430000000,
                )
            ),
            array(
                'identity_id' => 5912,
                'api_result' => array(
                    'response_code' => self::HTTP_NOT_FOUND,
                    'data' => array(
                        'error' => 'Not found'
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'response_code' => self::HTTP_NOT_FOUND,
                    'error' => 'Not found'
                )
            )
        );
    }
    /**
     * @dataProvider getProvider
     * @covers Datasift_Account_Identity::get
     */
    public function testGet($identityId, $apiResult, $expectedResult)
    {
        $identity = new DataSift_Account_Identity($this->_user);

        DataSift_MockApiClient::setResponse($apiResult);

        if (isset($expectedResult['error'])) {
            $this->setExpectedException('DataSift_Exception_APIError');
        }
        
        $result = $identity->get($identityId);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testCreate
     */
    public function createProvider()
    {
        return array(
            array(
                'label' => 'test',
                'master' => true,
                'active' => 'true',
                'api_result' => array(
                    'response_code' => self::HTTP_CREATED,
                    'data' => array(
                        'id' => 5912,
                        'api_key' => md5('test'),
                        'label' => 'test',
                        'status' => 'active',
                        'master' => true,
                        'created_at' => 1429002245,
                        'updated_at' => 1429002245,
                        'expires_at' => 1430000000,
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'id' => 5912,
                    'api_key' => md5('test'),
                    'label' => 'test',
                    'status' => 'active',
                    'master' => true,
                    'created_at' => 1429002245,
                    'updated_at' => 1429002245,
                    'expires_at' => 1430000000,
                )
            ),
            array(
                'label' => 'test',
                'master' => true,
                'active' => 'true',
                'api_result' => array(
                    'response_code' => self::HTTP_CONFLICT,
                    'data' => array(
                        'error' => 'An Identity with label test already exists'
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'response_code' => self::HTTP_CONFLICT,
                    'error' => 'An Identity with label test already exists'
                )
            )
        );
    }

    /**
     * @dataProvider createProvider
     * @covers Datasift_Account_Identity::create
     */
    public function testCreate($label, $master, $active, $apiResult, $expectedResult) 
    {
        $identity = new DataSift_Account_Identity($this->_user);

        DataSift_MockApiClient::setResponse($apiResult);

        if (isset($expectedResult['error'])) {
            $this->setExpectedException('DataSift_Exception_APIError');
        }
        
        $result = $identity->create($label, $master, $active);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testUpdate
     */
    public function updateProvider()
    {
        return array(
            array(
                'identity_id' => 5912,
                'label' => 'test',
                'master' => true,
                'active' => 'active',
                'api_result' => array(
                    'response_code' => self::HTTP_OK,
                    'data' => array(
                        'id' => 5912,
                        'api_key' => md5('test'),
                        'label' => 'test',
                        'status' => 'active',
                        'master' => true,
                        'created_at' => 1429002245,
                        'updated_at' => 1429002245,
                        'expires_at' => 1430000000,
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'id' => 5912,
                    'api_key' => md5('test'),
                    'label' => 'test',
                    'status' => 'active',
                    'master' => true,
                    'created_at' => 1429002245,
                    'updated_at' => 1429002245,
                    'expires_at' => 1430000000,
                )
            ),
            array(
                'identity_id' => 5912,
                'label' => 'test',
                'master' => true,
                'status' => 'active',
                'api_result' => array(
                    'response_code' => self::HTTP_NOT_FOUND,
                    'data' => array(
                        'error' => 'An Identity with the supplied id was not found',
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
                'label' => 'test',
                'master' => true,
                'status' => 'active',
                'api_result' => array(
                    'response_code' => self::HTTP_CONFLICT,
                    'data' => array(
                        'error' => 'An Identity with the label "test" already exists',
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'response_code' => self::HTTP_CONFLICT,
                    'error' => 'An Identity with the label "test" already exists'
                )
            ),
        );
    }

    /**
     * @dataProvider updateProvider
     * @covers Datasift_Account_Identity::update
     */
    public function testUpdate($identityId, $label, $master, $status, $apiResult, $expectedResult)
    {
        $identity = new DataSift_Account_Identity($this->_user);

        DataSift_MockApiClient::setResponse($apiResult);

        if (isset($expectedResult['error'])) {
            $this->setExpectedException('DataSift_Exception_APIError');
        }
        
        $result = $identity->update($identityId, $label, $master, $status);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testDelete
     */
    public function deleteProvider() 
    {
        return array(
            array(
                'identity_id' => 5912,
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
     * @dataProvider deleteProvider
     * @covers Datasift_Account_Identity::delete
     */
    public function testDelete($identityId, $apiResult, $expectedResult)
    {
        $identity = new DataSift_Account_Identity($this->_user);

        DataSift_MockApiClient::setResponse($apiResult);

        if (isset($expectedResult['error'])) {
            $this->setExpectedException('DataSift_Exception_APIError');
        }

        $result = $identity->delete($identityId);

        $this->assertEquals($expectedResult, $result);
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
     * @covers Datasift_Account_Identity_Token::getAll
     */
    public function testGetAllTokens($identityId, $page, $perPage, $apiResult, $expectedResult)
    {
        $identityToken = new DataSift_Account_Identity_Token($this->_user);

        DataSift_MockApiClient::setResponse($apiResult);

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
     * @covers Datasift_Account_Identity_Token::get
     */
    public function testGetToken($identityId, $service, $apiResult, $expectedResult)
    {
        $identityToken = new DataSift_Account_Identity_Token($this->_user);

        DataSift_MockApiClient::setResponse($apiResult);

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
     * @covers Datasift_Account_Identity_Token::create
     */
    public function testCreateToken($identityId, $service, $token, $apiResult, $expectedResult)
    {
        $identityToken = new DataSift_Account_Identity_Token($this->_user);

        DataSift_MockApiClient::setResponse($apiResult);

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
     * @covers Datasift_Account_Identity_Token::update
     */
    public function testUpdateToken($identityId, $service, $token, $apiResult, $expectedResult)
    {
        $identityToken = new DataSift_Account_Identity_Token($this->_user);

        DataSift_MockApiClient::setResponse($apiResult);

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
     * @covers Datasift_Account_Identity_Token::delete
     */
    public function testDeleteToken($identityId, $service, $apiResult, $expectedResult)
    {
        $identityToken = new DataSift_Account_Identity_Token($this->_user);

        DataSift_MockApiClient::setResponse($apiResult);

        if (isset($expectedResult['error'])) {
            $this->setExpectedException('DataSift_Exception_APIError');
        }

        $result = $identityToken->delete($identityId, $service);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testGetLimit
     */
    public function getLimitProvider()
    {
        return array(
            array(
                'identity_id' => 5912,
                'service' => 'test',
                'api_result' => array(
                    'response_code' => self::HTTP_OK,
                    'data' => array(
                        'identity_id' => 5912,
                        'service' => 'test',
                        'total_allowance' => 200
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'identity_id' => 5912,
                    'service' => 'test',
                    'total_allowance' => 200
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
                    'response_code' => self::HTTP_NOT_FOUND,
                    'data' => array(
                        'error' => 'A limit for test was not found for the Identity'
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'response_code' => self::HTTP_NOT_FOUND,
                    'error' => 'A limit for test was not found for the Identity'
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
        );     
    }


    /**
     * @dataProvider getLimitProvider
     * @covers Datasift_Account_Identity_Limit::get
     */
    public function testGetLimit($identityId, $service, $apiResult, $expectedResult)
    {
        $identityLimit = new DataSift_Account_Identity_Limit($this->_user);

        DataSift_MockApiClient::setResponse($apiResult);

        if (isset($expectedResult['error'])) {
            $this->setExpectedException('DataSift_Exception_APIError');
        }

        $result = $identityLimit->get($identityId, $service);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testGetAllLimits
     */
    public function getAllLimitsProvider()
    {
        return array(
            array(
                'service' => 'test',
                'page' => 1,
                'per_page' => 10,
                'api_result' => array(
                    'response_code' => self::HTTP_OK,
                    'data' => array(
                        'count' => 2,
                        'limits' => array(
                            array(
                                'identity_id' => 5912,
                                'service' => 'test',
                                'total_allowance' => 200,
                            ),
                            array(
                                'identity_id' => 4523,
                                'service' => 'test',
                                'total_allowance' => 200,
                            )
                        )
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'count' => 2,
                    'limits' => array(
                        array(
                            'identity_id' => 5912,
                            'service' => 'test',
                            'total_allowance' => 200,
                        ),
                        array(
                            'identity_id' => 4523,
                            'service' => 'test',
                            'total_allowance' => 200,
                        )
                    )
                )
            ),
            array(
                'service' => 'test',
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
                'service' => 'test',
                'page' => 1,
                'per_page' => 10,
                'api_result' => array(
                    'response_code' => self::HTTP_CONFLICT,
                    'data' => array(
                        'error' => 'A limit for test already exists for that Identity'
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'response_code' => self::HTTP_CONFLICT,
                    'error' => 'A limit for test already exists for that Identity'
                )
            ),
            array(
                'service' => 'test',
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
            ),
        );
    }

    /**
     * @dataProvider getAllLimitsProvider
     * @covers Datasift_Account_Identity_Limit::getAll
     */
    public function testGetAllLimits($service, $page, $perPage, $apiResult, $expectedResult)
    {
        $identityLimit = new DataSift_Account_Identity_Limit($this->_user);

        DataSift_MockApiClient::setResponse($apiResult);

        if (isset($expectedResult['error'])) {
            $this->setExpectedException('DataSift_Exception_APIError');
        }

        $result = $identityLimit->getAll($service, $page, $perPage);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testCreateLimit
     */
    public function createLimitProvider()
    {
        return array(
            array(
                'identity_id' => 5912,
                'service' => 'test',
                'limit' => 200,
                'analyze_queries' => 300,
                'api_result' => array(
                    'response_code' => self::HTTP_CREATED,
                    'data' => array(
                        'identity_id' => 5912,
                        'service' => 'test',
                        'total_allowance' => 200,
                        'analyze_queries' => 300
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'identity_id' => 5912,
                    'service' => 'test',
                    'total_allowance' => 200,
                    'analyze_queries' => 300
                )
            ),
            array(
                'identity_id' => 5912,
                'service' => 'test',
                'limit' => 200,
                'analyze_queries' => 300,
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
                'limit' => 200,
                'analyze_queries' => 300,
                'api_result' => array(
                    'response_code' => self::HTTP_CONFLICT,
                    'data' => array(
                        'error' => 'A limit for test already exists for that Identity'
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'response_code' => self::HTTP_CONFLICT,
                    'error' => 'A limit for test already exists for that Identity'
                )
            ),
            array(
                'identity_id' => 5912,
                'service' => 'test',
                'limit' => 200,
                'analyze_queries' => 300,
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
     * @dataProvider createLimitProvider
     * @covers Datasift_Account_Identity_Limit::create
     */
    public function testCreateLimit($identityId, $service, $limit, $analyze_queries, $apiResult, $expectedResult)
    {
        $identityLimit = new DataSift_Account_Identity_Limit($this->_user);

        DataSift_MockApiClient::setResponse($apiResult);

        if (isset($expectedResult['error'])) {
            $this->setExpectedException('DataSift_Exception_APIError');
        }

        $result = $identityLimit->create($identityId, $service, $limit, $analyze_queries);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testUpdateLimit
     */
    public function updateLimitProvider() 
    {
        return array(
            array(
                'identity_id' => 5912,
                'service' => 'test',
                'limit' => 200,
                'analyze_queries' => 300,
                'api_result' => array(
                    'response_code' => self::HTTP_OK,
                    'data' => array(
                        'identity_id' => 5912,
                        'service' => 'test',
                        'limit' => 200,
                        'analyze_queries' => 300
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'identity_id' => 5912,
                    'service' => 'test',
                    'limit' => 200,
                    'analyze_queries' => 300
                )
            ),
            array(
                'identity_id' => 5912,
                'service' => 'test',
                'limit' => 200,
                'analyze_queries' => 300,
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
                'limit' => 200,
                'analyze_queries' => 300,
                'api_result' => array(
                    'response_code' => self::HTTP_NOT_FOUND,
                    'data' => array(
                        'error' => 'A limit for test was not found for the Identity'
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'response_code' => self::HTTP_NOT_FOUND,
                    'error' => 'A limit for test was not found for the Identity'
                )
            ),
            array(
                'identity_id' => 5912,
                'service' => 'test',
                'limit' => 200,
                'analyze_queries' => 300,
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
     * @dataProvider updateLimitProvider
     * @covers Datasift_Account_Identity_Limit::update
     */
    public function testUpdateLimit($identityId, $service, $limit, $analyze_queries, $apiResult, $expectedResult)
    {
        $identityLimit = new DataSift_Account_Identity_Limit($this->_user);

        DataSift_MockApiClient::setResponse($apiResult);

        if (isset($expectedResult['error'])) {
            $this->setExpectedException('DataSift_Exception_APIError');
        }        

        $result = $identityLimit->update($identityId, $service, $limit, $analyze_queries);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testDeleteLimit
     */
    public function deleteLimitProvider() 
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
                        'error' => 'A limit for test was not found for the Identity'
                    ),
                    'rate_limit' => 200,
                    'rate_limit_remaining' => 150,
                ),
                'expected_result' => array(
                    'response_code' => self::HTTP_NOT_FOUND,
                    'error' => 'A limit for test was not found for the Identity'
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
     * @dataProvider deleteLimitProvider
     * @covers Datasift_Account_Identity_Limit::delete
     */
    public function testDeleteLimit($identityId, $service, $apiResult, $expectedResult)
    {
        $identityLimit = new DataSift_Account_Identity_Limit($this->_user);

        DataSift_MockApiClient::setResponse($apiResult);


        
    }
}
