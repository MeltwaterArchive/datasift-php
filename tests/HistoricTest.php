<?php
if (function_exists('date_default_timezone_set')) {
	date_default_timezone_set('UTC');
}

class HistoricTest extends PHPUnit_Framework_TestCase
{
	protected $user = false;

	protected function setUp()
	{
		require_once dirname(__FILE__) . '/../lib/datasift.php';
		require_once dirname(__FILE__) . '/../config.php';
		require_once dirname(__FILE__) . '/testdata.php';
		$this->user = new DataSift_User(USERNAME, API_KEY);
		$this->user->setApiClient('DataSift_MockApiClient');
		DataSift_MockApiClient::setResponse(false);
	}

	protected function set204Response()
	{
		$response = array(
			'response_code' => 204,
			'data'          => '',
			'rate_limit'           => 200,
			'rate_limit_remaining' => 150,
		);
		DataSift_MockApiClient::setResponse($response);
	}

	protected function setResponseToSingleHistoric($changes = array())
	{
		$response = array(
			'response_code' => 200,
			'data'          => array_merge(array(
				'id' => testdata('historic_playback_id'),
				'definition_id' => testdata('definition_hash'),
				'name' => testdata('historic_name'),
				'start' => testdata('historic_start_date'),
				'end' => testdata('historic_end_date'),
				'created_at' => 1334790000,
				'status' => testdata('historic_status'),
				'progress' => 0,
				'sources' => testdata('historic_sources'),
				'sample' => testdata('historic_sample'),
				'volume_info' => array(
					'digg' => 9,
				),
			), $changes),
			'rate_limit'           => 200,
			'rate_limit_remaining' => 150,
		);
		DataSift_MockApiClient::setResponse($response);
	}

	public function testConstructionFromHash()
	{
		$historic = $this->user->createHistoric(testdata('definition_hash'), testdata('historic_start_date'), testdata('historic_end_date'), testdata('historic_sources'), testdata('historic_name'));

		$this->assertInstanceOf(
			'DataSift_Historic',
			$historic,
			'DataSift_Historic construction failed'
		);
		$this->assertEquals($historic->getStreamHash(), testdata('definition_hash'), 'Definition hash is incorrect');
		$this->assertEquals($historic->getStartDate(), testdata('historic_start_date'), 'The start date is incorrect');
		$this->assertEquals($historic->getEndDate(), testdata('historic_end_date'), 'The end date is incorrect');
		$this->assertEquals($historic->getSources(), testdata('historic_sources'), 'The sources are incorrect');
		$this->assertEquals($historic->getName(), testdata('historic_name'), 'The name is incorrect');
		$this->assertEquals($historic->getSample(), 100, 'The default sample rate is incorrect');
	}

	public function testConstructionFromDefinition()
	{
		$def = $this->user->createDefinition(testdata('definition'));

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

		$historic = $def->createHistoric(testdata('historic_start_date'), testdata('historic_end_date'), testdata('historic_sources'), testdata('historic_name'), testdata('historic_sample'));

		$this->assertInstanceOf(
			'DataSift_Historic',
			$historic,
			'DataSift_Historic construction failed'
		);
		$this->assertEquals($historic->getStartDate(), testdata('historic_start_date'), 'The start date is incorrect');
		$this->assertEquals($historic->getEndDate(), testdata('historic_end_date'), 'The end date is incorrect');
		$this->assertEquals($historic->getSources(), testdata('historic_sources'), 'The sources are incorrect');
		$this->assertEquals($historic->getName(), testdata('historic_name'), 'The name is incorrect');
		$this->assertEquals($historic->getSample(), testdata('historic_sample'), 'The sample rate is incorrect');
	}

	public function testPrepare()
	{
		$historic = $this->user->createHistoric(testdata('definition_hash'), testdata('historic_start_date'), testdata('historic_end_date'), testdata('historic_sources'), testdata('historic_name'));

		$this->assertInstanceOf(
			'DataSift_Historic',
			$historic,
			'DataSift_Historic construction failed'
		);
		$this->assertEquals($historic->getStreamHash(), testdata('definition_hash'), 'Definition hash is incorrect');
		$this->assertEquals($historic->getStartDate(), testdata('historic_start_date'), 'The start date is incorrect');
		$this->assertEquals($historic->getEndDate(), testdata('historic_end_date'), 'The end date is incorrect');
		$this->assertEquals($historic->getSources(), testdata('historic_sources'), 'The sources are incorrect');
		$this->assertEquals($historic->getName(), testdata('historic_name'), 'The name is incorrect');
		$this->assertEquals($historic->getSample(), 100, 'The default sample rate is incorrect');

		$response = array(
			'response_code' => 200,
			'data'          => array(
				'dpus'         => testdata('historic_dpus'),
				'id'           => testdata('historic_playback_id'),
				'availability' => testdata('historic_availability'),
			),
			'rate_limit'           => 200,
			'rate_limit_remaining' => 150,
		);
		DataSift_MockApiClient::setResponse($response);

		$historic->prepare();

		$this->assertEquals($historic->getHash(), testdata('historic_playback_id'), 'The playback ID is incorrect');
		$this->assertEquals($historic->getDPUs(), testdata('historic_dpus'), 'The DPU cost is incorrect');
		$this->assertEquals($historic->getAvailability(), testdata('historic_availability'), 'The availability data is incorrect');

		// Make sure we can't prepare it twice
		$this->setExpectedException('DataSift_Exception_InvalidData');
		$historic->prepare();
	}

	public function testGet()
	{
		$this->setResponseToSingleHistoric();

		$historic = $this->user->getHistoric(testdata('historic_playback_id'));

		$this->assertInstanceOf(
			'DataSift_Historic',
			$historic,
			'DataSift_Historic construction failed'
		);
		$this->assertEquals($historic->getStreamHash(), testdata('definition_hash'), 'Definition hash is incorrect');
		$this->assertEquals($historic->getStartDate(), testdata('historic_start_date'), 'The start date is incorrect');
		$this->assertEquals($historic->getEndDate(), testdata('historic_end_date'), 'The end date is incorrect');
		$this->assertEquals($historic->getSources(), testdata('historic_sources'), 'The sources are incorrect');
		$this->assertEquals($historic->getName(), testdata('historic_name'), 'The name is incorrect');
		$this->assertEquals($historic->getSample(), testdata('historic_sample'), 'The default sample rate is incorrect');
	}

	public function testSetNameBeforePreparing()
	{
		$historic = $this->user->createHistoric(testdata('definition_hash'), testdata('historic_start_date'), testdata('historic_end_date'), testdata('historic_sources'), testdata('historic_name'));

		$this->assertInstanceOf(
			'DataSift_Historic',
			$historic,
			'DataSift_Historic construction failed'
		);
		$this->assertEquals($historic->getStreamHash(), testdata('definition_hash'), 'Definition hash is incorrect');
		$this->assertEquals($historic->getStartDate(), testdata('historic_start_date'), 'The start date is incorrect');
		$this->assertEquals($historic->getEndDate(), testdata('historic_end_date'), 'The end date is incorrect');
		$this->assertEquals($historic->getSources(), testdata('historic_sources'), 'The sources are incorrect');
		$this->assertEquals($historic->getName(), testdata('historic_name'), 'The name is incorrect');
		$this->assertEquals($historic->getSample(), 100, 'The default sample rate is incorrect');

		$newname = 'a new name';

		$historic->setName($newname);

		$this->assertEquals($historic->getName(), $newname, 'Failed to set the name before prepare');
	}

	public function testSetNameAfterPreparing()
	{
		$this->setResponseToSingleHistoric();

		$historic = $this->user->getHistoric(testdata('historic_playback_id'));

		$newname = 'a new name';

		$this->setResponseToSingleHistoric(array('name' => $newname));

		$historic->setName($newname);

		$this->assertEquals($historic->getName(), $newname, 'Failed to set the name after prepare');
	}

	public function testStart()
	{
		$historic = $this->user->createHistoric(testdata('definition_hash'), testdata('historic_start_date'), testdata('historic_end_date'), testdata('historic_sources'), testdata('historic_name'));

		$response = array(
			'response_code' => 200,
			'data'          => array(
				'dpus'         => testdata('historic_dpus'),
				'id'           => testdata('historic_playback_id'),
				'availability' => testdata('historic_availability'),
			),
			'rate_limit'           => 200,
			'rate_limit_remaining' => 150,
		);
		DataSift_MockApiClient::setResponse($response);

		$historic->prepare();

		$this->set204Response();

		$historic->start();
	}

	public function testStop()
	{
		$this->setResponseToSingleHistoric();

		$historic = $this->user->getHistoric(testdata('historic_playback_id'));

		$this->set204Response();

		$historic->stop();
	}

	public function testDelete()
	{
		$this->setResponseToSingleHistoric();

		$historic = $this->user->getHistoric(testdata('historic_playback_id'));

		$this->set204Response();

		$historic->delete();
	}
}
