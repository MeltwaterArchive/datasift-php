<?php
if (function_exists('date_default_timezone_set')) {
	date_default_timezone_set('UTC');
}

class DefinitionTest extends PHPUnit_Framework_TestCase
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

	public function testConstruction()
	{
		$def = $this->user->createDefinition();

		$this->assertInstanceOf(
			'DataSift_Definition',
			$def,
			'DataSift_Definition construction failed'
		);

		$this->assertEquals($def->get(), '', 'Default definition string is not empty');
	}

	public function testConstructionWithDefinition()
	{
		$def = $this->user->createDefinition(testdata('definition'));

		$this->assertInstanceOf(
			'DataSift_Definition',
			$def,
			'DataSift_Definition construction failed'
		);

		$this->assertEquals($def->get(), testdata('definition'), 'Definition string not set correctly');
	}

	public function testConstructionInvalidUser()
	{
		$this->setExpectedException('DataSift_Exception_InvalidData');
		$def = new DataSift_Definition('myusername');
	}

	public function testConstructionInvalidDefinition()
	{
		$this->setExpectedException('DataSift_Exception_InvalidData');
		$def = new DataSift_Definition($this->user, 1234);
	}

	public function testSetAndGet()
	{
		$def = new DataSift_Definition($this->user);

		$def->set(testdata('definition'));

		$this->assertEquals($def->get(), testdata('definition'), 'Definition string not set correctly');
	}

	public function testCompile_Success()
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

		$def = new DataSift_Definition($this->user, testdata('definition'));
		$this->assertEquals($def->get(), testdata('definition'), 'Definition string not set correctly');

		try {
			$def->compile();
		} catch (DataSift_Exception_InvalidData $e) {
			$this->fail('InvalidData: '.$e->getMessage().' ('.$e->getCode().')');
		} catch (DataSift_Exception_CompileFailed $e) {
			$this->fail('CompileFailed: '.$e->getMessage().' ('.$e->getCode().')');
		} catch (DataSift_Exception_APIError $e) {
			$this->fail('APIError: '.$e->getMessage().' ('.$e->getCode().')');
		}

		// The rate limit values should have been updated
		$this->assertEquals(200, $this->user->getRateLimit(), 'Incorrect rate limit');
		$this->assertEquals(150, $this->user->getRateLimitRemaining(), 'Incorrect rate limit remaining');

		$this->assertEquals(testdata('definition_hash'), $def->getHash(), 'Incorrect hash');
		// And a created_at date
		$this->assertEquals($response['data']['created_at'], date('Y-m-d H:i:s', $def->getCreatedAt()), 'Incorrect created_at date');
		// And a DPU
		$this->assertEquals($response['data']['dpu'], $def->getTotalDPU(), 'Incorrect total DPU');
	}

	public function testCompile_Failure()
	{
		$response = array(
			'response_code' => 400,
			'data'          => array(
				'error' => 'The target interactin.content does not exist',
			),
			'rate_limit'           => 200,
			'rate_limit_remaining' => 150,
		);
		DataSift_MockApiClient::setResponse($response);

		$this->setExpectedException('DataSift_Exception_CompileFailed');

		$def = new DataSift_Definition($this->user, testdata('invalid_definition'));
		$this->assertEquals($def->get(), testdata('invalid_definition'), 'Definition string not set correctly');

		try {
			$def->compile();
		} catch (DataSift_Exception_InvalidData $e) {
			$this->fail('InvalidData: '.$e->getMessage().' ('.$e->getCode().')');
		} catch (DataSift_Exception_APIError $e) {
			$this->fail('APIError: '.$e->getMessage().' ('.$e->getCode().')');
		}



	}

	public function testCompile_SuccessThenFailure()
	{
		$def = new DataSift_Definition($this->user, testdata('definition'));
		$this->assertEquals($def->get(), testdata('definition'), 'Definition string not set correctly');

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

		try {
			$def->compile();
		} catch (DataSift_Exception_CompileFailed $e) {
			$this->fail('CompileFailed: '.$e->getMessage().' ('.$e->getCode().')');
		} catch (DataSift_Exception_InvalidData $e) {
			$this->fail('InvalidData: '.$e->getMessage().' ('.$e->getCode().')');
		} catch (DataSift_Exception_APIError $e) {
			$this->fail('APIError: '.$e->getMessage().' ('.$e->getCode().')');
		}

		$this->assertEquals(
			$response['data']['hash'],
			$def->getHash(),
			'Hash is not correct'
		);

		// Now set the invalid definition in that same object
		$def->set(testdata('invalid_definition'));
		$this->assertEquals(testdata('invalid_definition'), $def->get(), 'Definition string not set correctly');

		$response = array(
			'response_code' => 400,
			'data'          => array(
				'error' => 'The target interactin.content does not exist',
			),
			'rate_limit'           => 200,
			'rate_limit_remaining' => 150,
		);
		DataSift_MockApiClient::setResponse($response);

		try {
			$def->compile();
			$this->fail('CompileFailed exception expected, but not thrown');
		} catch (DataSift_Exception_CompileFailed $e) {
			// Check the error message
			$this->assertEquals($e->getMessage(), $response['data']['error']);
		} catch (DataSift_Exception_InvalidData $e) {
			$this->fail('InvalidData: '.$e->getMessage().' ('.$e->getCode().')');
		} catch (DataSift_Exception_APIError $e) {
			$this->fail('APIError: '.$e->getMessage().' ('.$e->getCode().')');
		} catch (Exception $e) {
			$this->fail('Unhandled exception: '.$e->getMessage().' ('.$e->getCode().')');
		}
	}

	public function testGetCreatedAt()
	{
		$response = array(
			'response_code' => 200,
			'data'          => array(
				'created_at' => date('Y-m-d H:i:s', time()),
				'dpu'        => 10,
			),
			'rate_limit'           => 200,
			'rate_limit_remaining' => 150,
		);
		DataSift_MockApiClient::setResponse($response);

		$def = new DataSift_Definition($this->user, testdata('definition'));
		$this->assertEquals(testdata('definition'), $def->get(), 'Definition string not set correctly');

		$this->assertEquals(strtotime($response['data']['created_at']), $def->getCreatedAt(), 'The created_at date is incorrect');
	}

	public function testGetTotalDPU()
	{
		$response = array(
			'response_code' => 200,
			'data'          => array(
				'created_at' => date('Y-m-d H:i:s', time()),
				'dpu'        => 10,
			),
			'rate_limit'           => 200,
			'rate_limit_remaining' => 150,
		);
		DataSift_MockApiClient::setResponse($response);

		$def = new DataSift_Definition($this->user, testdata('definition'));
		$this->assertEquals($def->get(), testdata('definition'), 'Definition string not set correctly');

		$this->assertEquals($response['data']['dpu'], $def->getTotalDPU(), 'The total DPU is incorrect');
	}

	public function testGetDPUBreakdown()
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

		$def = new DataSift_Definition($this->user, testdata('definition'));
		$this->assertEquals($def->get(), testdata('definition'), 'Definition string not set correctly');
		$def->getHash();

		$response = array(
			'response_code' => 200,
			'data'          => array(
				'dpu' => array(
					'contains' => array(
						'count'   => 1,
						'dpu'     => 4,
						'targets' => array(
							'interaction.content' => array(
								'count' => 1,
								'dpu'   => 4,
							),
						),
					),
				),
				'dpu' => 4
			),
			'rate_limit'           => 200,
			'rate_limit_remaining' => 150,
		);
		DataSift_MockApiClient::setResponse($response);

		$dpu = $def->getDPUBreakdown();

		$this->assertEquals(array(), array_diff($dpu, $response['data']), 'The DPU breakdown is not what was expected');
		$this->assertEquals($response['data']['dpu'], 4, 'The total DPU is incorrect');
	}

	public function testGetBuffered()
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

		$def = new DataSift_Definition($this->user, testdata('definition'));
		$this->assertEquals($def->get(), testdata('definition'), 'Definition string not set correctly');
		$def->getHash();

		$response = array(
			'response_code' => 200,
			'data'          => array(
				'stream' => array(
					0 => array(
						'interaction' => array(
							'source' => 'Snaptu',
							'author' => array(
								'username' => 'nittolexia',
								'name'     => 'nittosoetreznoe',
								'id'       => 172192091,
								'avatar'   => 'http://a0.twimg.com/profile_images/1429378181/gendowor_normal.jpg',
								'link'     => 'http://twitter.com/nittolexia',
							),
							'type'       => 'twitter',
							'link'       => 'http://twitter.com/nittolexia/statuses/89571192838684672',
							'created_at' => 'Sat, 09 Jul 2011 05:46:51 +0000',
							'content'    => 'RT @ayyuchadel: Haha RT @nittolexia: Mending gak ush maen twitter dehh..RT @sansan_arie:',
							'id'         => '1e0a9eedc207acc0e074ea8aecb2c5ea',
						),
						'twitter' => array(
							'user' => array(
								'name'            => 'nittosoetreznoe',
								'description'     => 'fuck all',
								'location'        => 'denpasar, bali',
								'statuses_count'  => 6830,
								'followers_count' => 88,
								'friends_count'   => 111,
								'screen_name'     => 'nittolexia',
								'lang'            => 'en',
								'time_zone'       => 'Alaska',
								'id'              => 172192091,
								'geo_enabled'     => true,
							),
							'mentions' => array(
								0 => 'ayyuchadel',
								1 => 'nittolexia',
								2 => 'sansan_arie',
							),
							'id'         => '89571192838684672',
							'text'       => 'RT @ayyuchadel: Haha RT @nittolexia: Mending gak ush maen twitter dehh..RT @sansan_arie:',
							'source'     => '<a href="http://www.snaptu.com" rel="nofollow">Snaptu</a>',
							'created_at' => 'Sat, 09 Jul 2011 05:46:51 +0000',
						),
						'klout' => array(
							'score'         => 45,
							'network'       => 55,
							'amplification' => 17,
							'true_reach'    => 31,
							'slope'         => 0,
							'class'         => 'Networker',
						),
						'peerindex' => array(
							'score' => 30,
						),
						'language' => array(
							'tag' => 'da',
						),
					),
				),
			),
			'rate_limit'           => 200,
			'rate_limit_remaining' => 150,
		);
		DataSift_MockApiClient::setResponse($response);

		$interactions = $def->getBuffered();

		$this->assertEquals($response['data']['stream'], $interactions, 'Buffered interactions are not as expected');
	}

	public function testGetConsumer()
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

		$def = new DataSift_Definition($this->user, testdata('definition'));
		$this->assertEquals($def->get(), testdata('definition'));

		$consumer = $def->getConsumer(DataSift_StreamConsumer::TYPE_HTTP, new DummyEventHandler());
		$this->assertInstanceOf(
			'DataSift_StreamConsumer',
			$consumer,
			'Failed to get an HTTP stream consumer object'
		);
	}

	public function testGetDPUBreakdownOnInvalidDefinition()
	{
		$def = new DataSift_Definition($this->user, testdata('invalid_definition'));
		$this->assertEquals($def->get(), testdata('invalid_definition'));

		$response = array(
			'response_code' => 400,
			'data'          => array(
				'error' => 'The target interactin.content does not exist',
			),
			'rate_limit'           => 200,
			'rate_limit_remaining' => 150,
		);
		DataSift_MockApiClient::setResponse($response);

		try {
			$def->getDPUBreakdown();
			$this->fail('CompileFailed exception expected, but not thrown');
		} catch (DataSift_Exception_CompileFailed $e) {
			// Check the error message
			$this->assertEquals($e->getMessage(), $response['data']['error']);
		} catch (DataSift_Exception_InvalidData $e) {
			$this->fail('InvalidData: '.$e->getMessage().' ('.$e->getCode().')');
		} catch (DataSift_Exception_APIError $e) {
			$this->fail('APIError: '.$e->getMessage().' ('.$e->getCode().')');
		} catch (Exception $e) {
			$this->fail('Unhandled exception: '.$e->getMessage().' ('.$e->getCode().')');
		}
	}
}

require_once dirname(__FILE__) . '/../lib/DataSift/IStreamConsumerEventHandler.php';
class DummyEventHandler implements DataSift_IStreamConsumerEventHandler
{
	public function onConnect($consumer) { }
	public function onInteraction($consumer, $interaction, $hash) { }
	public function onDeleted($consumer, $interaction, $hash) { }
	public function onStatus($consumer, $type, $info) { }
	public function onWarning($consumer, $message) { }
	public function onError($consumer, $message) { }
	public function onDisconnect($consumer) { }
	public function onStopped($consumer, $reason) { }
}
