<?php
if (function_exists('date_default_timezone_set')) {
	date_default_timezone_set('UTC');
}

class PushTest extends PHPUnit_Framework_TestCase
{
	protected $user = false;
	protected $pd = false;

	protected function setUp()
	{
		require_once dirname(__FILE__) . '/../lib/datasift.php';
		require_once dirname(__FILE__) . '/../config.php';
		require_once dirname(__FILE__) . '/testdata.php';
		$this->user = new DataSift_User(USERNAME, API_KEY);
		$this->user->setApiClient('DataSift_MockApiClient');
		DataSift_MockApiClient::setResponse(false);
		$this->pd = new DataSift_Push_Definition($this->user);
	}

	protected function configurePushDefinition()
	{
		$this->pd->setOutputType(testdata('push_output_type'));
		foreach (testdata('push_output_params') as $key => $val) {
			$this->pd->setOutputParam($key, $val);
		}
	}

	protected function setResponseToASubscription()
	{
		$response = array(
			'response_code' => 200,
			'data'          => array(
				'id'					=> testdata('push_id'),
				'name'					=> testdata('push_name'),
				'created_at'			=> testdata('push_created_at'),
				'status'				=> testdata('push_status'),
				'hash'					=> testdata('definition_hash'),
				'hash_type'				=> testdata('push_hash_type'),
				'output_type'			=> testdata('push_output_type'),
				'output_params'			=> array(
					'delivery_frequency'	=> testdata('push_output_params', 'delivery_frequency'),
					'url'					=> testdata('push_output_params', 'url'),
					'auth'					=> array(
						'type'		=> testdata('push_output_params', 'auth.type'),
						'username'	=> testdata('push_output_params', 'auth.username'),
						'password'	=> testdata('push_output_params', 'auth.password'),
					),
				),
				'last_request'			=> testdata('push_last_request'),
				'last_success'			=> testdata('push_last_success'),
			),
			'rate_limit'           => 200,
			'rate_limit_remaining' => 150,
		);
		DataSift_MockApiClient::setResponse($response);
	}

	protected function checkSubscription($sub)
	{
		$this->assertEquals($sub->getId(), testdata('push_id'), 'The subscription ID is incorrect');
		$this->assertEquals($sub->getName(), testdata('push_name'), 'The subscription name is incorrect');
		$this->assertEquals($sub->getCreatedAt(), testdata('push_created_at'), 'The subscription created at timestamp is incorrect');
		$this->assertEquals($sub->getStatus(), testdata('push_status'), 'The subscription status is incorrect');
		$this->assertEquals($sub->getHash(), testdata('definition_hash'), 'The subscription hash is incorrect');
		$this->assertEquals($sub->getHashType(), testdata('push_hash_type'), 'The subscription hash type is incorrect');
		$this->assertEquals($sub->getOutputType(), testdata('push_output_type'), 'The subscription output type is incorrect');
		$this->assertEquals($sub->getOutputParam('delivery_frequency'), testdata('push_output_params', 'delivery_frequency'), 'The subscription delivery frequency is incorrect');
		$this->assertEquals($sub->getOutputParam('url'), testdata('push_output_params', 'url'), 'The subscription url is incorrect');
		$this->assertEquals($sub->getOutputParam('auth.type'), testdata('push_output_params', 'auth.type'), 'The subscription auth type is incorrect');
		$this->assertEquals($sub->getOutputParam('auth.username'), testdata('push_output_params', 'auth.username'), 'The subscription auth username is incorrect');
		$this->assertEquals($sub->getOutputParam('auth.password'), testdata('push_output_params', 'auth.password'), 'The subscription auth password is incorrect');
		$this->assertEquals($sub->getLastRequest(), testdata('push_last_request'), 'The subscription last request timestamp is incorrect');
		$this->assertEquals($sub->getLastSuccess(), testdata('push_last_success'), 'The subscription last success timestamp is incorrect');
	}

	public function testConstruction()
	{
		$this->assertInstanceOf(
			'DataSift_Push_Definition',
			$this->pd,
			'DataSift_Push_Definition construction failed'
		);
	}

	public function testInitialStatus()
	{
		$this->assertEquals($this->pd->getInitialStatus(), '', 'Default initial status is not empty');

		$this->pd->setInitialStatus(DataSift_Push_Subscription::STATUS_PAUSED);

		$this->assertEquals($this->pd->getInitialStatus(), DataSift_Push_Subscription::STATUS_PAUSED, 'Failed to set the initial status to paused');

		$this->pd->setInitialStatus(DataSift_Push_Subscription::STATUS_STOPPED);

		$this->assertEquals($this->pd->getInitialStatus(), DataSift_Push_Subscription::STATUS_STOPPED, 'Failed to set the initial status to stopped');
	}

	public function testOutputType()
	{
		$this->assertEquals($this->pd->getOutputType(), '', 'Default output type is not empty');

		$this->pd->setOutputType('http');

		$this->assertEquals($this->pd->getOutputType(), 'http', 'Failed to set the output type to http');
	}

	public function testOutputParams()
	{
		$this->assertNull($this->pd->getOutputParam('url'), 'Value of a non-set output parameter (url) is not null');

		$this->assertEquals(count($this->pd->getOutputParams()), 0, 'Initial number of output parameters is not 0');

		$this->pd->setOutputParam('url', 'http://www.example.com/');

		$this->assertEquals($this->pd->getOutputParam('url'), 'http://www.example.com/', 'Failed to set the url output param');

		$this->assertEquals(count($this->pd->getOutputParams()), 1, 'Number of output parameters is incorrect');
	}

	public function testValidation()
	{
		$this->configurePushDefinition();

		$response = array(
			'response_code' => 204,
			'data'          => '',
			'rate_limit'           => 200,
			'rate_limit_remaining' => 150,
		);
		DataSift_MockApiClient::setResponse($response);

		$this->pd->validate();
	}

	public function testSubscribeDefinition()
	{
		$def = $this->user->createDefinition(testdata('definition'));

		// Get the hash so we can fake that response
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
		$this->assertEquals($def->getHash(), testdata('definition_hash'), 'The definition hash is incorrect');

		$this->configurePushDefinition();

		$this->setResponseToASubscription();

		$subscription = $this->pd->subscribeDefinition($def, testdata('push_name'));

		$this->checkSubscription($subscription);
	}

	public function testSubscribeStreamHash()
	{
		$this->configurePushDefinition();

		$this->setResponseToASubscription();

		$subscription = $this->pd->subscribeStreamHash(testdata('definition_hash'), testdata('push_name'));

		$this->checkSubscription($subscription);
	}

	public function testSubscribeHistoric()
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

		$this->configurePushDefinition();

		$this->setResponseToASubscription();

		$subscription = $this->pd->subscribeHistoric($historic, testdata('push_name'));

		$this->checkSubscription($subscription);
	}

	public function testSubscribeHistoricPlaybackId()
	{
		$this->configurePushDefinition();

		$this->setResponseToASubscription();

		$subscription = $this->pd->subscribeHistoricPlaybackId(testdata('historic_playback_id'), testdata('push_name'));

		$this->checkSubscription($subscription);
	}
}






















