<?php

namespace DataSift\Tests;

use DataSift_Account_Identity;
use DataSift_Account_Identity_Limit;
use DataSift_Account_Identity_Token;
use DataSift_User;

class IdentityTest extends \PHPUnit_Framework_TestCase
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
        require_once(dirname(__FILE__) . '/../../lib/datasift.php');
        require_once(dirname(__FILE__) . '/../../config.php');

        $this->_user = new DataSift_User(USERNAME, API_KEY);
        $this->_user->setApiClient('\DataSift\Tests\MockApiClient');

        MockApiClient::setResponse(false);
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
            array(
                'label' => 'Test label',
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
     * @covers       Datasift_Account_Identity::getAll
     */
    public function testGetAll($label, $apiResult, $expectedResult)
    {
        $page = 1;
        $perPage = 10;

        $identity = new DataSift_Account_Identity($this->_user);

        MockApiClient::setResponse($apiResult);

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
     * @covers       Datasift_Account_Identity::get
     */
    public function testGet($identityId, $apiResult, $expectedResult)
    {
        $identity = new DataSift_Account_Identity($this->_user);

        MockApiClient::setResponse($apiResult);

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
     * @covers       Datasift_Account_Identity::create
     */
    public function testCreate($label, $master, $active, $apiResult, $expectedResult)
    {
        $identity = new DataSift_Account_Identity($this->_user);

        MockApiClient::setResponse($apiResult);

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
     * @covers       Datasift_Account_Identity::update
     */
    public function testUpdate($identityId, $label, $master, $status, $apiResult, $expectedResult)
    {
        $identity = new DataSift_Account_Identity($this->_user);

        MockApiClient::setResponse($apiResult);

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
     * @covers       Datasift_Account_Identity::delete
     */
    public function testDelete($identityId, $apiResult, $expectedResult)
    {
        $identity = new DataSift_Account_Identity($this->_user);

        MockApiClient::setResponse($apiResult);

        if (isset($expectedResult['error'])) {
            $this->setExpectedException('DataSift_Exception_APIError');
        }

        $result = $identity->delete($identityId);

        $this->assertEquals($expectedResult, $result);
    }
}
