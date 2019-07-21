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

    const MD5_HELLO = '5d41402abc4b2a76b9719d911017c592';
    /**
     * @var \Closure
     */
    private $functionA;
    /**
     * @var \Closure
     */
    private $functionB;
    /**
     * @var Experiment
     */
    private $experiment;

    public function setUp()
    {
        parent::setUp();

        $this->functionA = function () {
            return '5d41402abc4b2a76b9719d911017c592';
        };

        $this->functionB = function () {
            usleep(500);
            return 'b';
        };

        $this->experiment = new Experiment('pl-0-unit-test', null, new DebugMetrics());
        $this->experiment->control($this->functionA);
        $this->experiment->candidate($this->functionB);
    }

    public function testControlResultIsReturnedWhenTrialFails()
    {
        $result = $this->experiment->run();

        $this->assertSame(self::MD5_HELLO, $result);
    }

    public function testControlResultIsReturnedWhenTrialSucceeds()
    {
        $this->experiment->candidate(function () {
            return self::MD5_HELLO;
        });

        $result = $this->experiment->run();

        $this->assertSame(self::MD5_HELLO, $result);
    }

    public function testControlResultIsReturnedWhenTrialThrowsAnException()
    {
        $this->experiment->candidate(function () {
            throw new \RuntimeException('an error occurred');
        });

        $result = $this->experiment->run();

        $this->assertSame(self::MD5_HELLO, $result);
    }

    public function testExecuteReturnsControlResult()
    {
        $result = Experiment::execute('pl-0-unit-test', function (Experiment $exp) {
            $exp->control($this->functionA);
            $exp->candidate(function () {
                throw new \RuntimeException('an error occurred');
            });
        }, new DebugMetrics());

        $this->assertSame(self::MD5_HELLO, $result);
    }
}
