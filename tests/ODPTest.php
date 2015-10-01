<?php
if (function_exists('date_default_timezone_set')){
	date_default_timezone_set('UTC');
}

class OdpTest extends PHPUnit_Framework_TestCase
{
	protected $user = false;
	protected $source_id = false;

	protected function setUp()
	{
		require_once(dirname(_FILE_).'/../lib/datasift.php');
		require_once(dirname(_FILE_).'/../config.php');
		$this->user = new DataSift_User(USERNAME, API_KEY);
		$this->user->setApiClient('DataSift_MockApiClient');
		$this->$odp = new DataSift_ODP($user, $source_id, $data_set);
		DataSift_MockApiClient::setResponse(false);
	}

	public function testSourceLength(){

		$source_id= '1f1be6565a1d4ef38f9f4aeec9554440'

		$setSource = DataSift_ODP::setSourceId($this->user, $source_id);

		$this->assertCount(32, $source_id, 'Hash does not meet the required length');
	}

	public function testDataIsJson(){
		
		$data = array(
 			array('id' => '234',
 				'body' => 'yo'), array(
 				'id' => '898',
 				'body' => 'hey'));
		

		$getSource = DataSift_ODP::get($this->user, '1f1be6565a1d4ef38f9f4aeec9554440');

		$this->assertJsonStringEqualsJsonString({{"id": "234", "body": "yo"},{"id": "898", "body": "hey"}}, json_encode($data), 'Data doesnt appear to be JSON');
	}

	public function testNoSource(){
		$odp = new DataSift_ODP($this->user);

		$this->setExpectedException('DataSift_Exception_InvalidData');

		$source_id = '';

		$odp->ingest($source_id);
	}

	public function testIngest(){
		$response = array(
			'Cache-Control' => 'private',
			'Content-Type' => 'application/json',
			'Server' => 'DataSift Ingestion/1.0',
			'Content-Length' => '64',
			'Ingestion-Data-Ratelimit-Reset' => '0',
			'Ingestion-Request-Ratelimit-Limit' => '10000',
			'Ingestion-Request-Ratelimit-Reset' => '0',
			'Ingestion-Data-Ratelimit-Remaining' => '614400',
			'Ingestion-Request-Ratelimit-Reset-Ttl' => '1443176541',
			'Ingestion-Data-Ratelimit-Reset-Ttl' => '1443176541',
			'Ingestion-Data-Ratelimit-Limit' => '614400',
			'Ingestion-Request-Ratelimit-Remaining' => '10000',
			'Strict-Transport-Security' => 'max-age=31536000'
		);

		DataSift_MockApiClient::setResponse($response);

		$data = array(
 			array('id' => '234',
 				'body' => 'yo'), array(
 				'id' => '898',
 				'body' => 'hey'));

		$hash = '1f1be6565a1d4ef38f9f4aeec9554440'

		$ingest = DataSift_ODP::ingest($this->user, $hash, $data);

		$this->assertEquals($ingest['source_id'], $hash, 'Hash did not match');
		$this->assertEquals($ingest['data'], $data, 'Data not valid');
		
	}
}

?>