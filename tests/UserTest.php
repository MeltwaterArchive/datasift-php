<?php
class UserTest extends PHPUnit_Framework_TestCase
{
	protected $config = false;
	protected $user = false;

	protected function setUp()
	{
		require_once(dirname(__FILE__).'/../lib/datasift.php');
		require_once(dirname(__FILE__).'/../config.php');
		require_once(dirname(__FILE__).'/testdata.php');
		$this->user = new DataSift_User(USERNAME, API_KEY);
		$this->user->setApiClient('DataSift_MockApiClient');
		DataSift_MockApiClient::setResponse(false);
	}

	public function testConstruction()
	{
		$this->assertInstanceOf(
			'DataSift_User',
			$this->user,
			'DataSift_User construction failed'
		);

		$this->assertEquals(USERNAME, $this->user->getUsername(), 'Username is incorrect');

		$this->assertEquals(API_KEY, $this->user->getAPIKey(), 'API key is incorrect');
	}

	public function testCreateDefinition_Empty()
	{
		$def = $this->user->createDefinition();

		$this->assertInstanceOf(
			'DataSift_Definition',
			$def,
			'Failed to create an empty definition'
		);

		$this->assertEquals($def->get(), '', 'Definition is not empty');
	}

	public function testCreateDefinition_NonEmpty()
	{
		$def = $this->user->createDefinition(testdata('definition'));

		$this->assertInstanceOf(
			'DataSift_Definition',
			$def,
			'Failed to create a non-empty definition'
		);

		$this->assertEquals($def->get(), testdata('definition'), 'Definition is incorrect');
	}

	public function testRateLimits()
	{
		$response = array(
			'response_code' => 200,
			'data'          => array(
				'hash'       => testdata('definition_hash'),
				'created_at' => date('Y-m-d H:i:s', time()),
				'cost'       => 10,
			),
			'rate_limit'           => 200,
			'rate_limit_remaining' => 150,
		);
		DataSift_MockApiClient::setResponse($response);

		$def = $this->user->createDefinition(testdata('definition'));

		try {
			$def->compile();
		} catch (DataSift_Exception_InvalidData $e) {
			$this->fail('InvalidData: '.$e->getMessage().' ('.$e->getCode().')');
		} catch (DataSift_Exception_CompileFailed $e) {
			// Ignore this, irrelevant to this test
		} catch (DataSift_Exception_APIError $e) {
			// Ignore this, irrelevant to this test
		} catch (DataSift_Exception_RateLimitExceeded $e) {
			// Ignore this, irrelevant to this test
		}

		$this->assertEquals($this->user->getRateLimit(), 200, 'Rate limit is incorrect');
		$this->assertEquals($this->user->getRateLimitRemaining(), 150, 'Rate limit remaining is incorrect');
	}

	public function testGetUsage()
	{
		$response = array(
			'response_code' => 200,
			'data' => json_decode('{"processed":9999,"delivered":10800,"streams":[{"hash":"a123ab20f37f333824159b8868ad3827","processed":7505,"delivered":8100},{"hash":"c369ab20f37f333824159b8868ad3827","processed":2494,"delivered":2700}]}', true),
			'rate_limit' => 200,
			'rate_limit_remaining' => 150,
		);
		DataSift_MockApiClient::setResponse($response);

		$usage = $this->user->getUsage();
		$this->assertEquals($response['data'], $usage, 'Usage data for the past 24 hours is not as expected');

		$usage = $this->user->getUsage(time() - (86400 * 2), time() - 86400);
		$this->assertEquals($response['data'], $usage, 'Usage data for 24 hours from 48 hours ago is not as expected');
	}

	public function testGetUsageWithInvalidStart()
	{
		$this->setExpectedException('DataSift_Exception_InvalidData');
		$usage = $this->user->getUsage(-500, time());
	}

	public function testGetUsageWithInvalidEnd()
	{
		$this->setExpectedException('DataSift_Exception_InvalidData');
		$usage = $this->user->getUsage(time(), -500);
	}

	public function testGetUsageWithEndBeforeStart()
	{
		$this->setExpectedException('DataSift_Exception_InvalidData');
		$usage = $this->user->getUsage(time(), time() - 86400);
	}

	public function testGetUsageApiErrors()
	{
		// Bad request from user supplied data
		try {
			$response = array(
				'response_code' => 400,
				'data'          => array(
					'error' => 'Bad request from user supplied data',
				),
				'rate_limit'           => 200,
				'rate_limit_remaining' => 150,
			);
			DataSift_MockApiClient::setResponse($response);
			$usage = $this->user->getUsage();
			// Should have had an exception
			$this->fail('Expected ApiErrir exception did not get thrown');
		} catch (DataSift_Exception_ApiError $e) {
			$this->assertEquals($response['data']['error'], $e->getMessage(), 'Exception message is not as expected');
		}

		// Unauthorised or banned
		try {
			$response = array(
				'response_code' => 401,
				'data'          => array(
					'error' => 'User banned because they are a very bad person',
				),
				'rate_limit'           => 200,
				'rate_limit_remaining' => 150,
			);
			DataSift_MockApiClient::setResponse($response);
			$usage = $this->user->getUsage();
			// Should have had an exception
			$this->fail('Expected ApiErrir exception did not get thrown');
		} catch (DataSift_Exception_AccessDenied $e) {
			$this->assertEquals($response['data']['error'], $e->getMessage(), 'Exception message is not as expected');
		}

		// Endpoint or data not found
		try {
			$response = array(
				'response_code' => 404,
				'data'          => array(
					'error' => 'Endpoint or data not found',
				),
				'rate_limit'           => 200,
				'rate_limit_remaining' => 150,
			);
			DataSift_MockApiClient::setResponse($response);
			$usage = $this->user->getUsage();
			// Should have had an exception
			$this->fail('Expected ApiErrir exception did not get thrown');
		} catch (DataSift_Exception_ApiError $e) {
			$this->assertEquals($response['data']['error'], $e->getMessage(), 'Exception message is not as expected');
		}

		// Problem with an internal service
		try {
			$response = array(
				'response_code' => 500,
				'data'          => array(
					'error' => 'Problem with an internal service',
				),
				'rate_limit'           => 200,
				'rate_limit_remaining' => 150,
			);
			DataSift_MockApiClient::setResponse($response);
			$usage = $this->user->getUsage();
			// Should have had an exception
			$this->fail('Expected ApiErrir exception did not get thrown');
		} catch (DataSift_Exception_ApiError $e) {
			$this->assertEquals($response['data']['error'], $e->getMessage(), 'Exception message is not as expected');
		}
	}
}
