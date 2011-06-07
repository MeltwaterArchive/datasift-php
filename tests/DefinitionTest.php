<?php
class DefinitionTest extends PHPUnit_Framework_TestCase
{
	protected $user = false;

	protected function setUp()
	{
		require_once dirname(__FILE__) . '/../lib/datasift.php';
		require_once dirname(__FILE__) . '/../config.php';
		require_once dirname(__FILE__) . '/testdata.php';
		$this->user = new DataSift_User(USERNAME, API_KEY);
	}

	public function testConstruction()
	{
		$def = new DataSift_Definition($this->user);

		$this->assertInstanceOf(
			'DataSift_Definition',
			$def,
			'DataSift_Definition construction failed'
		);

		$this->assertEquals($def->get(), '', 'Default definition string is not empty');
	}

	public function testConstructionWithDefinition()
	{
		$def = new DataSift_Definition($this->user, testdata('definition'));

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

		assertTrue($def->getHash() === false, 'Hash is not false');
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

	public function testGetConsumer()
	{
		$def = new DataSift_Definition($this->user, testdata('definition'));
		$this->assertEquals($def->get(), testdata('definition'));

		$consumer = $def->getConsumer();
		$this->assertInstanceOf(
			'DataSift_StreamConsumer',
			$consumer,
			'Failed to get an HTTP stream consumer object'
		);
	}
}
