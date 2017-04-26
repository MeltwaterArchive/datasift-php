<?php

namespace DataSift\Tests\Exception;

class NotYetImplementedTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function should_return_a_custom_exception_message()
    {
        $exception = new \DataSift_Exception_NotYetImplemented();
        $this->assertEquals(
            "Not yet implemented (" . __DIR__ . "/NotYetImplementedTest.php:10)",
            $exception->getMessage()
        );
    }
}
