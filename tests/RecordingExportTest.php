<?php
	class RecordingExportTest extends PHPUnit_Framework_TestCase
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
			$edata = testdata('export');
			$export = new DataSift_RecordingExport($this->user, $edata);

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

		public function testConstructionViaAPI()
		{
			// Create the object
			$edata = testdata('export');

			$response = array(
				'response_code' => 200,
				'data' => $edata,
				'rate_limit' => 200,
				'rate_limit_remaining' => 150,
			);
			DataSift_MockApiClient::setResponse($response);

			$export = new DataSift_RecordingExport($this->user, $edata['id']);

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

		public function testDelete()
		{
			$export = new DataSift_RecordingExport($this->user, testdata('export'));

			$response = array(
				'response_code' => 200,
				'data' => json_decode('{"success":true}', true),
				'rate_limit' => 200,
				'rate_limit_remaining' => 150,
			);
			DataSift_MockApiClient::setResponse($response);

			// Delete the recording
			$export->delete();

			// Check that the object prevents access
			$this->setExpectedException('DataSift_Exception_InvalidData');
			$export->getID();
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
				$export = new DataSift_RecordingExport($this->user, testdata('export'));
				$export->delete();
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
				$export = new DataSift_RecordingExport($this->user, testdata('export'));
				$export->delete();
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
				$export = new DataSift_RecordingExport($this->user, testdata('export'));
				$export->delete();
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
				$export = new DataSift_RecordingExport($this->user, testdata('export'));
				$export->delete();
				// Should have had an exception
				$this->fail('Expected ApiError exception did not get thrown');
			} catch (DataSift_Exception_ApiError $e) {
				$this->assertEquals($response['data']['error'], $e->getMessage(), '500 exception message is not as expected');
			}
		}
	}
