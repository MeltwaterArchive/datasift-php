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

		$this->assertEquals($this->user->getRateLimit(), 200, 'Rate limit is incorrect');
		$this->assertEquals($this->user->getRateLimitRemaining(), 150, 'Rate limit remaining is incorrect');
	}

	public function testGetUsage()
	{
		$response = array(
			'response_code' => 200,
			'data' => json_decode('{"processed":9999,"delivered":10800,"streams":{"a123ab20f37f333824159b8868ad3827":{"processed":7505,"delivered":8100},"c369ab20f37f333824159b8868ad3827":{"processed":2494,"delivered":2700}}}', true),
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

	public function testGetRecordings()
	{
		$response = array(
			'response_code' => 200,
			'data' => json_decode('{"count":"1","recordings":[{"id":"47ce46821c942ff42f8e","start_time":1313055762,"finish_time":null,"name":"Inherit everything 123","hash":"9e2e0ba334ee76aa06ef42d5565dbb70"}]}', true),
			'rate_limit' => 200,
			'rate_limit_remaining' => 150,
		);
		DataSift_MockApiClient::setResponse($response);

		// This will be the object we expect
		$expected_recording = new DataSift_Recording($this->user, $response['data']['recordings'][0]);

		// Make the call
		$recordings = $this->user->getRecordings();

		// Check the result
		$this->assertEquals($expected_recording, $recordings[0], 'Recording object is not as expected');
	}

	public function testGetRecordingsErrors()
	{
		// Invalid page argument
		try {
			$response = array(
				'response_code' => 200,
				'data' => json_decode('{"count":"0","recordings":[]}', true),
				'rate_limit' => 200,
				'rate_limit_remaining' => 150,
			);
			DataSift_MockApiClient::setResponse($response);

			$this->user->getRecordings(false);
			$this->fail('Expected InvalidData exception due to an invalid page argument did not get thrown');
		} catch (DataSift_Exception_InvalidData $e) {
			// This is the expected exception
		}

		// Invalid count argument
		try {
			$response = array(
				'response_code' => 200,
				'data' => json_decode('{"count":"0","recordings":[]}', true),
				'rate_limit' => 200,
				'rate_limit_remaining' => 150,
			);
			DataSift_MockApiClient::setResponse($response);

			$this->user->getRecordings(1, false);
			// Should have had an exception
			$this->fail('Expected InvalidData exception due to an invalid count argument did not get thrown');
		} catch (DataSift_Exception_InvalidData $e) {
			// This is the expected exception
		}

		// Invalid page and count arguments
		try {
			$response = array(
				'response_code' => 200,
				'data' => json_decode('{"count":"0","recordings":[]}', true),
				'rate_limit' => 200,
				'rate_limit_remaining' => 150,
			);
			DataSift_MockApiClient::setResponse($response);

			$this->user->getRecordings('abcd', true);
			// Should have had an exception
			$this->fail('Expected InvalidData exception due to invalid page and count arguments did not get thrown');
		} catch (DataSift_Exception_InvalidData $e) {
			// This is the expected exception
		}

		// Bad request from user supplied data
		try {
			$response = array(
				'response_code' => 400,
				'data' => array(
					'error' => 'Bad request from user supplied data',
				),
				'rate_limit' => 200,
				'rate_limit_remaining' => 150,
			);
			DataSift_MockApiClient::setResponse($response);

			$this->user->getRecordings();
			// Should have had an exception
			$this->fail('Expected ApiError exception did not get thrown');
		} catch (DataSift_Exception_ApiError $e) {
			$this->assertEquals($response['data']['error'], $e->getMessage(), '400 exception message is not as expected');
		}

		// Unauthorised or banned
		try {
			$response = array(
				'response_code' => 401,
				'data' => array(
					'error' => 'User banned because they are a very bad person',
				),
				'rate_limit' => 200,
				'rate_limit_remaining' => 150,
			);
			DataSift_MockApiClient::setResponse($response);

			$this->user->getRecordings();
			// Should have had an exception
			$this->fail('Expected AccessDenied exception did not get thrown');
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

	public function testGetRecording()
	{
		$response = array(
			'response_code' => 200,
			'data' => json_decode('{"id":"47ce46821c942ff42f8e","start_time":1313055762,"finish_time":null,"name":"Inherit everything 123","hash":"9e2e0ba334ee76aa06ef42d5565dbb70"}', true),
			'rate_limit' => 200,
			'rate_limit_remaining' => 150,
		);
		DataSift_MockApiClient::setResponse($response);

		// This will be the object we expect
		$expected_recording = new DataSift_Recording($this->user, $response['data']);

		// Make the call
		$recording = $this->user->getRecording('47ce46821c942ff42f8e');

		// Check the result
		$this->assertEquals($expected_recording, $recording, 'Recording object is not as expected');
	}

	public function testGetRecordingErrors()
	{
		// Invalid id argument - boolean
		try {
			$response = array(
				'response_code' => 200,
				'data' => array(),
				'rate_limit' => 200,
				'rate_limit_remaining' => 150,
			);
			DataSift_MockApiClient::setResponse($response);

			$this->user->getRecording(false);
			$this->fail('Expected InvalidData exception due to an invalid id argument did not get thrown');
		} catch (DataSift_Exception_InvalidData $e) {
			// This is the expected exception
		}

		// Invalid count argument
		try {
			$response = array(
				'response_code' => 200,
				'data' => array(),
				'rate_limit' => 200,
				'rate_limit_remaining' => 150,
			);
			DataSift_MockApiClient::setResponse($response);

			$this->user->getRecording(1234);
			// Should have had an exception
			$this->fail('Expected InvalidData exception due to an invalid id argument did not get thrown');
		} catch (DataSift_Exception_InvalidData $e) {
			// This is the expected exception
		}

		// Bad request from user supplied data
		try {
			$response = array(
				'response_code' => 400,
				'data' => array(
					'error' => 'Bad request from user supplied data',
				),
				'rate_limit' => 200,
				'rate_limit_remaining' => 150,
			);
			DataSift_MockApiClient::setResponse($response);

			$this->user->getRecording('abcd');
			// Should have had an exception
			$this->fail('Expected ApiError exception did not get thrown');
		} catch (DataSift_Exception_ApiError $e) {
			$this->assertEquals($response['data']['error'], $e->getMessage(), '400 exception message is not as expected');
		}

		// Unauthorised or banned
		try {
			$response = array(
				'response_code' => 401,
				'data' => array(
					'error' => 'User banned because they are a very bad person',
				),
				'rate_limit' => 200,
				'rate_limit_remaining' => 150,
			);
			DataSift_MockApiClient::setResponse($response);

			$this->user->getRecording('abcd');
			// Should have had an exception
			$this->fail('Expected AccessDenied exception did not get thrown');
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

			$this->user->getRecording('abcd');
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

			$this->user->getRecording('abcd');
			// Should have had an exception
			$this->fail('Expected ApiError exception did not get thrown');
		} catch (DataSift_Exception_ApiError $e) {
			$this->assertEquals($response['data']['error'], $e->getMessage(), '500 exception message is not as expected');
		}
	}

	public function testScheduleRecording()
	{
		$response = array(
			'response_code' => 200,
			'data' => json_decode('{"id":"79b0e0217227d3f1d237","start_time":1313401533,"finish_time":1333878300,"name":"ApiDocs","hash":"9e2e0ba334ee76aa06ef42d5565dbb70"}', true),
			'rate_limit' => 200,
			'rate_limit_remaining' => 150,
		);
		DataSift_MockApiClient::setResponse($response);

		// This will be the object we expect
		$expected_recording = new DataSift_Recording($this->user, $response['data']);

		// Make the call
		$recording = $this->user->scheduleRecording('9e2e0ba334ee76aa06ef42d5565dbb70', 'ApiDocs', 1313401533, 1333878300);

		// Check the result
		$this->assertEquals($expected_recording, $recording, 'Recording object is not as expected');
	}

	public function testScheduleRecordingErrors()
	{
		// Invalid hash argument
		try {
			$response = array(
				'response_code' => 200,
				'data' => array(),
				'rate_limit' => 200,
				'rate_limit_remaining' => 150,
			);
			DataSift_MockApiClient::setResponse($response);

			$this->user->scheduleRecording(false);
			$this->fail('Expected InvalidData exception due to an invalid hash argument did not get thrown');
		} catch (DataSift_Exception_InvalidData $e) {
			// This is the expected exception
		}

		// Invalid name argument
		try {
			$response = array(
				'response_code' => 200,
				'data' => array(),
				'rate_limit' => 200,
				'rate_limit_remaining' => 150,
			);
			DataSift_MockApiClient::setResponse($response);

			$this->user->scheduleRecording('abcd', false);
			// Should have had an exception
			$this->fail('Expected InvalidData exception due to an invalid name argument did not get thrown');
		} catch (DataSift_Exception_InvalidData $e) {
			// This is the expected exception
		}

		// Invalid start argument
		try {
			$response = array(
				'response_code' => 200,
				'data' => array(),
				'rate_limit' => 200,
				'rate_limit_remaining' => 150,
			);
			DataSift_MockApiClient::setResponse($response);

			$this->user->scheduleRecording('abcd', 'efgh', false);
			// Should have had an exception
			$this->fail('Expected InvalidData exception due to an invalid start argument did not get thrown');
		} catch (DataSift_Exception_InvalidData $e) {
			// This is the expected exception
		}

		// Invalid end argument
		try {
			$response = array(
				'response_code' => 200,
				'data' => array(),
				'rate_limit' => 200,
				'rate_limit_remaining' => 150,
			);
			DataSift_MockApiClient::setResponse($response);

			$this->user->scheduleRecording('abcd', 'efgh', time(), false);
			// Should have had an exception
			$this->fail('Expected InvalidData exception due to an invalid end argument did not get thrown');
		} catch (DataSift_Exception_InvalidData $e) {
			// This is the expected exception
		}

		// Bad request from user supplied data
		try {
			$response = array(
				'response_code' => 400,
				'data' => array(
					'error' => 'Bad request from user supplied data',
				),
				'rate_limit' => 200,
				'rate_limit_remaining' => 150,
			);
			DataSift_MockApiClient::setResponse($response);

			$this->user->scheduleRecording('abcd', 'efgh', time(), time() + 3600);
			// Should have had an exception
			$this->fail('Expected ApiError exception did not get thrown');
		} catch (DataSift_Exception_ApiError $e) {
			$this->assertEquals($response['data']['error'], $e->getMessage(), '400 exception message is not as expected');
		}

		// Unauthorised or banned
		try {
			$response = array(
				'response_code' => 401,
				'data' => array(
					'error' => 'User banned because they are a very bad person',
				),
				'rate_limit' => 200,
				'rate_limit_remaining' => 150,
			);
			DataSift_MockApiClient::setResponse($response);

			$this->user->scheduleRecording('abcd', 'efgh', time(), time() + 3600);
			// Should have had an exception
			$this->fail('Expected AccessDenied exception did not get thrown');
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

			$this->user->scheduleRecording('abcd', 'efgh', time(), time() + 3600);
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

			$this->user->scheduleRecording('abcd', 'efgh', time(), time() + 3600);
			// Should have had an exception
			$this->fail('Expected ApiError exception did not get thrown');
		} catch (DataSift_Exception_ApiError $e) {
			$this->assertEquals($response['data']['error'], $e->getMessage(), '500 exception message is not as expected');
		}
	}

	public function testGetExports()
	{
		$response = array(
			'response_code' => 200,
			'data' => json_decode('{"count":"1","exports":[{"id":"102","recording_id":"47ce46821c942ff42f8e","name":"Unnamed export 47ce46821c942ff42f8e","start":1313055762,"end":1313405342,"status":"killed"}]}', true),
			'rate_limit' => 200,
			'rate_limit_remaining' => 150,
		);
		DataSift_MockApiClient::setResponse($response);

		// Create the exported export object
		$expected_exports = array(new DataSift_RecordingExport($this->user, $response['data']['exports'][0]));

		// Make the call
		$exports = $this->user->getExports();

		// Check the result
		$this->assertEquals($expected_exports, $exports, 'Exports array is not as expected');
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

	public function testGetUsageWithInvalidPeriod()
	{
		$this->setExpectedException('DataSift_Exception_InvalidData');
		$usage = $this->user->getUsage(time());
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
