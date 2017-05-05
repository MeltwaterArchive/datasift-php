<?php

namespace DataSift\Tests;

use \Mockery as m;

class AccountTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function usageProvider()
    {
        return array(
            array(array()),
            array(array('start' => 1493977828), 1493977828),
            array(array('end' => 1493977800), false, 1493977800),
            array(array('start' => 1493977828, 'end' => 1493977800), 1493977828, 1493977800),
            array(array('period' => 'period'), false, false, 'period'),
            array(
                array('start' => 1493977828, 'end' => 1493977800, 'period' => 'period'),
                1493977828,
                1493977800,
                'period'
            )
        );
    }

    /**
     * @dataProvider usageProvider
     * @test
     *
     * @param array    $params
     * @param int|bool $start
     * @param int|bool $end
     * @param mixed    $period
     */
    public function test_usage_endpoint($params, $start = false, $end = false, $period = null)
    {
        $user = m::mock('\DataSift_User');
        $user->shouldReceive('get')
            ->with(
                'account/usage',
                $params
            );

        $account = new \DataSift_Account($user);
        $account->usage($start, $end, $period);
    }
}
