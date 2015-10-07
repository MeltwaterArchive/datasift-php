<?php

if (function_exists('date_default_timezone_set')){
	date_default_timezone_set('UTC');
}

class OdpTest extends PHPUnit_Framework_TestCase
{
	protected $user = false;
	protected $source_id = false;
	protected $odp = false;

	protected function setUp()
	{
		require_once(dirname(_FILE_).'/../lib/datasift.php');
		require_once(dirname(_FILE_).'/../config.php');
		$this->user = new DataSift_User(USERNAME, API_KEY);
		$this->user->setApiClient('DataSift_MockApiClient');
		//$this->odp = new DataSift_ODP($this->user, '1f1be6565a1d4ef38f9f4aeec9554440');
		DataSift_MockApiClient::setResponse(false);
	}

	public function testSourceLength(){
		$odp = new DataSift_ODP($this->user, '1f1be6565a1d4ef38f9f4aeec9554440');

		$this->assertEquals($odp->getSourceId(), '1f1be6565a1d4ef38f9f4aeec9554440', 'Hash does not meet the required length');
	}

	public function testNoSource(){
		$odp = new DataSift_ODP($this->user, '');

		$this->setExpectedException('DataSift_Exception_InvalidData');

		$data_set = '[{"id": "234", "body": "yo"}]';

		$odp->ingest($data_set);
	}

	public function testNoData(){
		$odp = new DataSift_ODP($this->user, '1f1be6565a1d4ef38f9f4aeec9554440');

		$this->setExpectedException('DataSift_Exception_InvalidData');

		$data_set = '';

		$odp->ingest($data_set);
	}

	public function testIngest(){
		$response = array(
			'response_code' => 200,
			'data' => array(
			'accepted' => 1,
			'total_message_bytes' => 1788,
			),
		);

		DataSift_MockApiClient::setResponse($response);

		$data_set = '[{"id": "234", "body": "yo"}]';

		$source_id = '1f1be6565a1d4ef38f9f4aeec9554440';

		//$ingest = DataSift_ODP::ingest($this->user, $source_id)->ingest($data_set);

		$ingest = new DataSift_ODP($this->user, $source_id);
		$response = $ingest->ingest($data_set);


		$this->assertEquals($response['accepted'], 1, 'Not accepted');
		
	}
}

?>