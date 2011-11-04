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
}
