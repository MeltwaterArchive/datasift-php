<?php

namespace DataSift\Tests;

use DataSift_Account_Identity_Limit;
use DataSift_User;
use \Mockery as m;

class LimitTest extends \PHPUnit_Framework_TestCase
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

    public function tearDown()
    {
        m::close();
    }

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
     * @covers       Datasift_Account_Identity_Limit::get
     */
    public function testGetLimit($identityId, $service, $apiResult, $expectedResult)
    {
        $identityLimit = new DataSift_Account_Identity_Limit($this->_user);

        MockApiClient::setResponse($apiResult);

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
     * @covers       Datasift_Account_Identity_Limit::getAll
     */
    public function testGetAllLimits($service, $page, $perPage, $apiResult, $expectedResult)
    {
        $identityLimit = new DataSift_Account_Identity_Limit($this->_user);

        MockApiClient::setResponse($apiResult);

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
     * @covers       Datasift_Account_Identity_Limit::create
     */
    public function testCreateLimit($identityId, $service, $limit, $analyze_queries, $apiResult, $expectedResult)
    {
        $identityLimit = new DataSift_Account_Identity_Limit($this->_user);

        MockApiClient::setResponse($apiResult);

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
     * @covers       Datasift_Account_Identity_Limit::update
     */
    public function testUpdateLimit($identityId, $service, $limit, $analyze_queries, $apiResult, $expectedResult)
    {
        $identityLimit = new DataSift_Account_Identity_Limit($this->_user);

        MockApiClient::setResponse($apiResult);

        if (isset($expectedResult['error'])) {
            $this->setExpectedException('DataSift_Exception_APIError');
        }

        $result = $identityLimit->update($identityId, $service, $limit, $analyze_queries);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @covers       Datasift_Account_Identity_Limit::delete
     */
    public function test_delete_limit()
    {
        $identity = md5(microtime(true));
        $service = 'service';

        $user = m::mock('\DataSift_User');
        $user->shouldReceive('delete')
            ->with(
                'account/identity/' . $identity . '/limit/' . $service
            );

        $identityLimit = new DataSift_Account_Identity_Limit($user);
        $identityLimit->delete($identity, $service);
    }
}
