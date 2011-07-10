<?php
class LiveApiTest extends PHPUnit_Framework_TestCase
{
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
		$this->assertTrue($def->getHash() == testdata('definition_hash'), 'Incorrect hash');
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

		$this->assertEquals($def->getHash(), testdata('definition_hash'), 'Hash is not correct');

		// Now set the invalid definition in that same object
		$def->set(testdata('invalid_definition'));
		$this->assertEquals($def->get(), testdata('invalid_definition'), 'Definition string not set correctly');
		$this->assertTrue($def->getHash() === false, 'Hash is not false');

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

		$this->assertTrue($def->getHash() === false, 'Hash is not false');
	}

	public function testGetCreatedAt()
	{
		$def = new DataSift_Definition($this->user, testdata('definition'));
		$this->assertEquals($def->get(), testdata('definition'), 'Definition string not set correctly');

		$created_at = $def->getCreatedAt();

		$this->assertTrue($created_at > strtotime('2000-01-01'), 'The created_at date is earlier than Jan 1st, 2000');
	}

	public function testGetTotalCost()
	{
		$def = new DataSift_Definition($this->user, testdata('definition'));
		$this->assertEquals($def->get(), testdata('definition'), 'Definition string not set correctly');

		$cost = $def->getTotalCost();

		$this->assertTrue($cost > 0, 'The total cost is not positive');
	}

	public function testGetCostBreakdown()
	{
		$def = new DataSift_Definition($this->user, testdata('definition'));
		$this->assertEquals($def->get(), testdata('definition'), 'Definition string not set correctly');

		$cost = $def->getCostBreakdown();

		$this->assertEquals(count($cost['costs']), 1, 'The cost breakdown is not what was expected');
		$this->assertTrue($cost['total'] > 0, 'The total cost is not positive');
	}

	public function testGetBuffered()
	{
		$def = new DataSift_Definition($this->user, testdata('definition'));
		$this->assertEquals($def->get(), testdata('definition'), 'Definition string not set correctly');

		$interactions = $def->getBuffered();

		$this->assertTrue(is_array($interactions), 'Failed to get buffered interactions');
	}
}