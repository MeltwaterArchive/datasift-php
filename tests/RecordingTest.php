<?php
	class RecordingTest extends PHPUnit_Framework_TestCase
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
			// Create the object
			$rdata = testdata('recording');
			$recording = new DataSift_Recording($this->user, $rdata);

			// Check we have the right object type
			$this->assertInstanceOf(
				'DataSift_Recording',
				$recording,
				'DataSift_Recording construction failed'
			);

			// Check the contents of the object
			$this->assertEquals($rdata['id'], $recording->getID(), 'Recording ID is incorrect');
			$this->assertEquals($rdata['start_time'], $recording->getStartTime(), 'Recording start time is incorrect');
			$this->assertEquals($rdata['finish_time'], $recording->getEndTime(), 'Recording finish time is incorrect');
			$this->assertEquals($rdata['name'], $recording->getName(), 'Recording name is incorrect');
			$this->assertEquals($rdata['hash'], $recording->getHash(), 'Recording hash is incorrect');
		}

		public function testUpdate()
		{
			$rdata = testdata('recording');
			$recording = new DataSift_Recording($this->user, $rdata);
			$this->assertEquals($rdata['name'], $recording->getName(), 'Recording name is incorrect');

			// Update the name
			$response = array(
				'response_code' => 200,
				'data' => $rdata,
				'rate_limit' => 200,
				'rate_limit_remaining' => 150,
			);
			$response['data']['name'] = 'New name';
			DataSift_MockApiClient::setResponse($response);
			$recording->update(array('name' => $response['data']['name']));
			$this->assertEquals($response['data']['name'], $recording->getName(), 'Recording name is incorrect after update');

			// Update the start time
			$response = array(
				'response_code' => 200,
				'data' => $rdata,
				'rate_limit' => 200,
				'rate_limit_remaining' => 150,
			);
			$response['data']['start_time'] = time() + 3600;
			DataSift_MockApiClient::setResponse($response);
			$recording->update(array('start_time' => $response['data']['start_time']));
			$this->assertEquals($response['data']['start_time'], $recording->getStartTime(), 'Recording start time is incorrect after update');

			// Update the end time
			$response = array(
				'response_code' => 200,
				'data' => $rdata,
				'rate_limit' => 200,
				'rate_limit_remaining' => 150,
			);
			$response['data']['finish_time'] = time() + 7200;
			DataSift_MockApiClient::setResponse($response);
			$recording->update(array('finish_time' => $response['data']['finish_time']));
			$this->assertEquals($response['data']['finish_time'], $recording->getEndTime(), 'Recording end time is incorrect after update');
		}

		public function testUpdateWithInvalidParameter()
		{
			$this->setExpectedException('DataSift_Exception_InvalidData');

			$recording = new DataSift_Recording($this->user, testdata('recording'));
			$recording->update(false);
		}

		public function testUpdateWithInvalidKey()
		{
			$this->setExpectedException('DataSift_Exception_InvalidData');

			$recording = new DataSift_Recording($this->user, testdata('recording'));
			$recording->update(array('this key' => 'is not valid'));
		}

		public function testUpdateWithInvalidValue()
		{
			$this->setExpectedException('DataSift_Exception_InvalidData');

			$recording = new DataSift_Recording($this->user, testdata('recording'));
			$recording->update(array('id' => true));
		}

		public function testUpdateApiErrors()
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
				$recording = new DataSift_Recording($this->user, testdata('recording'));
				$recording->update(array('name' => 'New name'));
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
				$recording = new DataSift_Recording($this->user, testdata('recording'));
				$recording->update(array('name' => 'New name'));
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
				$recording = new DataSift_Recording($this->user, testdata('recording'));
				$recording->update(array('name' => 'New name'));
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
				$recording = new DataSift_Recording($this->user, testdata('recording'));
				$recording->update(array('name' => 'New name'));
				// Should have had an exception
				$this->fail('Expected ApiError exception did not get thrown');
			} catch (DataSift_Exception_ApiError $e) {
				$this->assertEquals($response['data']['error'], $e->getMessage(), '500 exception message is not as expected');
			}
		}

		public function testDelete()
		{
			$recording = new DataSift_Recording($this->user, testdata('recording'));

			$response = array(
				'response_code' => 200,
				'data' => json_decode('{"success":true}', true),
				'rate_limit' => 200,
				'rate_limit_remaining' => 150,
			);
			DataSift_MockApiClient::setResponse($response);

			// Delete the recording
			$recording->delete();

			// Check that the object prevents access
			$this->setExpectedException('DataSift_Exception_InvalidData');
			$recording->getID();
		}

		public function testDeleteApiErrors()
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
				$recording = new DataSift_Recording($this->user, testdata('recording'));
				$recording->delete();
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
				$recording = new DataSift_Recording($this->user, testdata('recording'));
				$recording->delete();
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
				$recording = new DataSift_Recording($this->user, testdata('recording'));
				$recording->delete();
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
				$recording = new DataSift_Recording($this->user, testdata('recording'));
				$recording->delete();
				// Should have had an exception
				$this->fail('Expected ApiError exception did not get thrown');
			} catch (DataSift_Exception_ApiError $e) {
				$this->assertEquals($response['data']['error'], $e->getMessage(), '500 exception message is not as expected');
			}
		}

		public function testStartExport()
		{
			$recording = new DataSift_Recording($this->user, testdata('recording'));
			$edata = testdata('export');

			$response = array(
				'response_code' => 200,
				'data' => $edata,
				'rate_limit' => 200,
				'rate_limit_remaining' => 150,
			);
			DataSift_MockApiClient::setResponse($response);

			$export = $recording->startExport(DataSift_RecordingExport::FORMAT_JSON);

			// Check we have the right object type
			$this->assertInstanceOf(
				'DataSift_RecordingExport',
				$export,
				'DataSift_RecordingExport construction failed'
			);

			// Check the contents of the object
			$this->assertEquals($edata['id'], $export->getID(), 'Export ID is incorrect');
			$this->assertEquals($edata['recording_id'], $export->getRecordingID(), 'Recording ID is incorrect');
			$this->assertEquals($edata['start'], $export->getStart(), 'Export start time is incorrect');
			$this->assertEquals($edata['end'], $export->getEnd(), 'Export end time is incorrect');
			$this->assertEquals($edata['status'], $export->getStatus(), 'Export status is incorrect');
		}

		public function testStartExportWithInvalidFormat()
		{
			$this->setExpectedException('DataSift_Exception_InvalidData');

			$recording = new DataSift_Recording($this->user, testdata('recording'));
			$recording->startExport('abcd');
		}

		public function testStartExportWithInvalidStartBoolean()
		{
			$this->setExpectedException('DataSift_Exception_InvalidData');

			$recording = new DataSift_Recording($this->user, testdata('recording'));
			$recording->startExport(DataSift_RecordingExport::FORMAT_XLS, 'recording name', true);
		}

		public function testStartExportWithInvalidNameBoolean()
		{
			$this->setExpectedException('DataSift_Exception_InvalidData');

			$recording = new DataSift_Recording($this->user, testdata('recording'));
			$recording->startExport(DataSift_RecordingExport::FORMAT_XLS, true);
		}

		public function testStartExportWithInvalidNameInteger()
		{
			$this->setExpectedException('DataSift_Exception_InvalidData');

			$recording = new DataSift_Recording($this->user, testdata('recording'));
			$recording->startExport(DataSift_RecordingExport::FORMAT_XLS, 12345);
		}

		public function testStartExportWithInvalidStartString()
		{
			$this->setExpectedException('DataSift_Exception_InvalidData');

			$recording = new DataSift_Recording($this->user, testdata('recording'));
			$recording->startExport(DataSift_RecordingExport::FORMAT_XLS, 'recording name', 'abcde');
		}

		public function testStartExportWithInvalidEndBoolean()
		{
			$this->setExpectedException('DataSift_Exception_InvalidData');

			$rdata = testdata('recording');
			$recording = new DataSift_Recording($this->user, $rdata);
			$recording->startExport(DataSift_RecordingExport::FORMAT_XLS, 'recording name', $rdata['start'], true);
		}

		public function testStartExportWithInvalidEndString()
		{
			$this->setExpectedException('DataSift_Exception_InvalidData');

			$rdata = testdata('recording');
			$recording = new DataSift_Recording($this->user, $rdata);
			$recording->startExport(DataSift_RecordingExport::FORMAT_XLS, 'recording name', $rdata['start'], 'abcde');
		}

		public function testStartExportWithInvalidStartTooEarly()
		{
			$this->setExpectedException('DataSift_Exception_InvalidData');

			$rdata = testdata('recording');
			$recording = new DataSift_Recording($this->user, $rdata);
			$recording->startExport(DataSift_RecordingExport::FORMAT_XLS, 'recording name', $rdata['start'] - 86400);
		}

		public function testStartExportWithInvalidStartTooLate()
		{
			$this->setExpectedException('DataSift_Exception_InvalidData');

			$rdata = testdata('recording');
			$recording = new DataSift_Recording($this->user, $rdata);
			$recording->startExport(DataSift_RecordingExport::FORMAT_XLS, 'recording name', $rdata['finish'] + 86400);
		}

		public function testStartExportWithInvalidEndTooEarly()
		{
			$this->setExpectedException('DataSift_Exception_InvalidData');

			$rdata = testdata('recording');
			$recording = new DataSift_Recording($this->user, $rdata);
			$recording->startExport(DataSift_RecordingExport::FORMAT_XLS, 'recording name', $rdata['start'], $rdata['start'] - 86400);
		}

		public function testStartExportWithInvalidEndTooLate()
		{
			$this->setExpectedException('DataSift_Exception_InvalidData');

			$rdata = testdata('recording');
			$recording = new DataSift_Recording($this->user, $rdata);
			$recording->startExport(DataSift_RecordingExport::FORMAT_XLS, 'recording name', $rdata['start'], $rdata['finish'] + 86400);
		}

		public function testStartExportWithInvalidEndBeforeStart()
		{
			$this->setExpectedException('DataSift_Exception_InvalidData');

			$rdata = testdata('recording');
			$recording = new DataSift_Recording($this->user, $rdata);
			$recording->startExport(DataSift_RecordingExport::FORMAT_XLS, 'recording name', $rdata['finish'], $rdata['start']);
		}

		public function testStartExportApiErrors()
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
				$recording = new DataSift_Recording($this->user, testdata('recording'));
				$recording->startExport(DataSift_RecordingExport::FORMAT_JSON);
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
				$recording = new DataSift_Recording($this->user, testdata('recording'));
				$recording->startExport(DataSift_RecordingExport::FORMAT_JSON);
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
				$recording = new DataSift_Recording($this->user, testdata('recording'));
				$recording->startExport(DataSift_RecordingExport::FORMAT_JSON);
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
				$recording = new DataSift_Recording($this->user, testdata('recording'));
				$recording->startExport(DataSift_RecordingExport::FORMAT_JSON);
				// Should have had an exception
				$this->fail('Expected ApiError exception did not get thrown');
			} catch (DataSift_Exception_ApiError $e) {
				$this->assertEquals($response['data']['error'], $e->getMessage(), '500 exception message is not as expected');
			}
		}
	}
