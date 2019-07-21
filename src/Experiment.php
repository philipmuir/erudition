<?php
/**
 * Wtf
 */
namespace Erudition;

use Exception;
use RuntimeException;
use Throwable;

/**
 * Class Experiment
 *
 * @category Class - full phpcs does not bring me joy
 * @package  Erudition
 * @author   Philip Muir <me@pm.id.au>
 * @license  https://github.com/philipmuir/erudition/blob/master/LICENSE MIT
 * @link     https://github.com/philipmuir/erudition
 */
class Experiment
{
    /** @var string */
    private $experimentName;

    /** @var callable */
    protected $control;
    /** @var array */
    protected $controlParams = [];

    /** @var callable */
    protected $candidate;
    /** @var array */
    protected $candidateParams = [];

    /** @var callable|null  */
    protected $comparator = null;

    /**
     * Cunt
     *
     * @param string   $experimentName Loggable experiment name
     * @param callable $callable
     *
     * @return Result
     * @throws Throwable
     */
    public static function execute(string $experimentName, callable $callable)
    {
        $instance = new self($experimentName);
        call_user_func_array($callable, array($instance));
        return $instance->run($experimentName);
    }

    /**
     * Experiment constructor.
     * @param string $experimentName
     * @param callable $comparator
     */
    public function __construct(string $experimentName, callable $comparator = null)
    {
        $this->experimentName = $experimentName;
        $this->comparator     = $comparator;
    }

    /**
     * Runs the control and candidate code, returning the control results.
     * Note: Throws a runtime exception if either codepath is missing.
     *
     * @return Result
     * @throws Throwable
     */
    public function run()
    {
        if (!is_callable($this->control) || !is_callable($this->candidate)) {
            throw new RuntimeException('Missing control or candidate callable');
        }

        // randomise order in which the two paths are called.
        if (random_int(0, 1) === 1) {
            $controlResult   = $this->runTrial('control', $this->control, $this->controlParams);
            $candidateResult = $this->runTrial('candidate', $this->candidate, $this->candidateParams);
        } else {
            $candidateResult = $this->runTrial('candidate', $this->candidate, $this->candidateParams);
            $controlResult   = $this->runTrial('control', $this->control, $this->controlParams);
        }

        $this->logResults($controlResult, $candidateResult);

        if ($controlResult->isException()) {
            throw $controlResult->getException();
        }

        return $controlResult->getValue();
    }

    /**
     * @param string $trialName
     * @param callable $callable
     * @param array $params
     * @return Result
     */
    private function runTrial(string $trialName, callable $callable, array $params): Result
    {
        return $this->observeCallable(
            $this->experimentName,
            $trialName,
            $callable,
            $params
        );
    }

    /**
     * A
     *
     * @param string   $experimentName a
     * @param string   $trial          a
     * @param callable $callable       a
     * @param array    $params         a
     *
     * @return Result
     */
    protected function observeCallable($experimentName, $trial, &$callable, $params)
    {
        $result = $exception = null;

        $start = microtime(true);
        try {
            $result = call_user_func_array($callable, $params);
        } catch (Exception $e) {
            $exception = $e;
        }
        $duration = microtime(true) - $start;

        return new Result($experimentName, $trial, $result, $duration, $exception);
    }

    /**
     * A
     *
     * @param Result $a A
     * @param Result $b B
     *
     * @return bool
     */
    protected function compare($a, $b)
    {
        if (is_callable($this->comparator)) {
            return call_user_func_array($this->comparator, array($a->getResult(), $b->getResult()));
        }

        return $this->defaultCompare($a->getResult(), $b->getResult());
    }

    /**
     * Defaults compare function using assertEquals from PHPUnit.
     *
     * @param Result $control
     * @param Result $trial
     * @return bool
     */
    protected function defaultCompare(Result $control, Result $trial)
    {
        // TODO(philipmuir): move compare functions to own interface and have phpunit comparisons as an option
        //        $factory = new Factory;
        //        $comparator = $factory->getComparatorFor($a, $b);
        //
        //        try {
        //            return $comparator->assertEquals($a, $b);
        //        }  catch (ComparisonFailure $failure) {
        //            return false;
        //        }
        return $control->getResult() === $trial->getResult();
    }

    /**
     * A
     *
     * @param Result $controlResult   aaa
     * @param Result $candidateResult aaa
     *
     * @return null
     */
    protected function logResults($controlResult, $candidateResult)
    {
        //todo: syslog errors/StatsD counts
        if (! $this->compare($controlResult->getResult(), $candidateResult->getResult())) {
            // die
        }

        return;
    }

    /**
     * A
     *
     * @param callable $callable Call me
     * @param null     $params   Params
     *
     * @return $this
     */
    public function control(callable $callable, $params = null)
    {
        $this->control = $callable;
        $this->controlParams = $params;

        return $this;
    }

    /**
     * B
     *
     * @param callable $callable callable
     * @param null     $params   function params
     *
     * @return $this
     */
    public function candidate(callable $callable, $params = null)
    {
        $this->candidate = $callable;
        $this->candidateParams = $params;

        return $this;
    }

    /**
     * B
     *
     * @param callable $callable callableeee
     *
     * @return $this
     */
    public function comparator(callable $callable)
    {
        $this->comparator = $callable;
        return $this;
    }
}
