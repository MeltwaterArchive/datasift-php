<?php
if (function_exists('date_default_timezone_set')) {
	date_default_timezone_set('UTC');
}

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
				'dpu'        => 10,
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

		$this->assertNotEquals($this->user->getRateLimit(), -1, 'Rate limit is -1 after calling the API');
		$this->assertNotEquals($this->user->getRateLimitRemaining(), -1, 'Rate limit remaining is -1 after calling the API');
	}

	public function testGetUsage()
	{
		$response = array(
			'response_code' => 200,
			'data' => json_decode('{"start":"Mon, 07 Nov 2011 10:25:00 +0000","end":"Mon, 07 Nov 2011 11:25:00 +0000","streams":{"6fd9d61afba0149e0f1d42080ccd9075":{"licenses":{"twitter":3},"seconds":300}}}', true),
			'rate_limit' => 200,
			'rate_limit_remaining' => 150,
		);
		DataSift_MockApiClient::setResponse($response);

		$usage = $this->user->getUsage();
		$this->assertEquals($response['data'], $usage, 'Usage data for the specified hour is not as expected');

		$response = array(
			'response_code' => 200,
			'data' => json_decode('{"start":"Mon, 06 Nov 2011 11:25:00 +0000","end":"Mon, 07 Nov 2011 11:25:00 +0000","streams":{"6fd9d61afba0149e0f1d42080ccd9075":{"licenses":{"twitter":1354},"seconds":34035}}}', true),
			'rate_limit' => 200,
			'rate_limit_remaining' => 150,
		);
		DataSift_MockApiClient::setResponse($response);

		$usage = $this->user->getUsage('day');
		$this->assertEquals($response['data'], $usage, 'Usage data for the specified day is not as expected');
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
			$this->fail('Expected ApiError exception did not get thrown');
		} catch (DataSift_Exception_ApiError $e) {
			$this->assertEquals($response['data']['error'], $e->getMessage(), '400 exception message is not as expected');
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
			$this->fail('Expected ApiError exception did not get thrown');
		} catch (DataSift_Exception_AccessDenied $e) {
			$this->assertEquals($response['data']['error'], $e->getMessage(), '401 exception message is not as expected');
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
			$this->fail('Expected ApiError exception did not get thrown');
		} catch (DataSift_Exception_ApiError $e) {
			$this->assertEquals($response['data']['error'], $e->getMessage(), '404 exception message is not as expected');
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
			$this->fail('Expected ApiError exception did not get thrown');
		} catch (DataSift_Exception_ApiError $e) {
			$this->assertEquals($response['data']['error'], $e->getMessage(), '500 exception message is not as expected');
		}
	}
}
