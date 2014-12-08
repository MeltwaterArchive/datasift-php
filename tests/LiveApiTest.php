<?php
if (function_exists('date_default_timezone_set')) {
	date_default_timezone_set('UTC');
}

class LiveApiTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var DataSift_User
	 */
	protected $user = false;

	protected function setUp()
	{
		require_once(dirname(__FILE__).'/../lib/datasift.php');
		require_once(dirname(__FILE__).'/../config.php');
		require_once(dirname(__FILE__).'/testdata.php');
		$this->user = new DataSift_User(USERNAME, API_KEY);
	}

	public function testCompile_Success()
	{
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

		// We should now have a hash
		$this->assertEquals(testdata('definition_hash'), $def->getHash(), 'Incorrect hash');
	}

	public function testCompile_Failure()
	{
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

		$this->assertTrue($def->getHash() === false, 'Hash is not false');
	}

	public function testCompile_SuccessThenFailure()
	{
		$def = new DataSift_Definition($this->user, testdata('definition'));
		$this->assertEquals($def->get(), testdata('definition'), 'Definition string not set correctly');

		try {
			$def->compile();
		} catch (DataSift_Exception_CompileFailed $e) {
			$this->fail('CompileFailed: '.$e->getMessage().' ('.$e->getCode().')');
		} catch (DataSift_Exception_InvalidData $e) {
			$this->fail('InvalidData: '.$e->getMessage().' ('.$e->getCode().')');
		} catch (DataSift_Exception_APIError $e) {
			$this->fail('APIError: '.$e->getMessage().' ('.$e->getCode().')');
		}

		$this->assertEquals(testdata('definition_hash'), $def->getHash(), 'Hash is not correct');

		// Now set the invalid definition in that same object
		$def->set(testdata('invalid_definition'));
		$this->assertEquals($def->get(), testdata('invalid_definition'), 'Definition string not set correctly');

		try {
			$def->compile();
			$this->fail('CompileFailed exception expected, but not thrown');
		} catch (DataSift_Exception_CompileFailed $e) {
			// Do nothing because this is what's supposed to happen
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
		$def = new DataSift_Definition($this->user, testdata('definition'));
		$this->assertEquals($def->get(), testdata('definition'), 'Definition string not set correctly');

		$created_at = $def->getCreatedAt();

		$this->assertTrue($created_at > strtotime('2000-01-01'), 'The created_at date is earlier than Jan 1st, 2000');
	}

	public function testGetTotalDPU()
	{
		$def = new DataSift_Definition($this->user, testdata('definition'));
		$this->assertEquals($def->get(), testdata('definition'), 'Definition string not set correctly');

		$dpu = $def->getTotalDPU();

		$this->assertTrue($dpu > 0, 'The total DPU is not positive');
	}

	public function testGetDPUBreakdown()
	{
		$def = new DataSift_Definition($this->user, testdata('definition'));
		$this->assertEquals($def->get(), testdata('definition'), 'Definition string not set correctly');
		$dpu = $def->getDPUBreakdown();

		$this->assertEquals(count($dpu['detail']), 1, 'The DPU breakdown is not what was expected');
		$this->assertTrue($dpu['dpu'] > 0, 'The total DPU is not positive');
		$this->assertEquals($dpu['dpu'], $def->getTotalDPU(), 'The total DPU returned by the definition is not correct');
	}

	public function testGetBuffered()
	{
		$def = new DataSift_Definition($this->user, testdata('definition'));
		$this->assertEquals($def->get(), testdata('definition'), 'Definition string not set correctly');

		$interactions = $def->getBuffered();

		$this->assertTrue(is_array($interactions), 'Failed to get buffered interactions');
	}

	public function testGetUsage()
	{
		$usage = $this->user->getUsage();
		$this->assertTrue(isset($usage['start']), 'Usage data does not contain a start date');
		$this->assertTrue(isset($usage['end']), 'Usage data does not contain a start date');
		$this->assertInternalType('array', $usage['streams'], 'Usage data does not contain a valid stream array');
	}
}