<?php

namespace Erudition\Test\Unit;

use Erudition\DebugMetrics;
use Erudition\Experiment;
use PHPUnit\Framework\TestCase;

/**
 * Class ResultTest
 * @package Erudition\Test\Unit
 */
class ResultTest extends TestCase
{

    public function testCake()
    {
        $functionA = function () {
            return 'a';
        };
        $functionB = function () {
            usleep(500);
            return 'b';
        };

        $experiment = new Experiment('pl-0-unit-test', null, new DebugMetrics());
        $experiment->control($functionA);
        $experiment->candidate($functionB);

        $result = $experiment->run();

        $this->assertSame('a', $result);
    }
}
