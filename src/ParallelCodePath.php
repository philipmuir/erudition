<?php
namespace Erudition;

use RuntimeException;
use SebastianBergmann\Comparator\Factory;
use SebastianBergmann\Comparator\ComparisonFailure;
use Throwable;


class ParallelCodePath
{

    protected $control;
    protected $controlParams = array();

    protected $candidate;
    protected $candidateParams = array();

    protected $comparator = null;

    /**
     * @param $experimentName
     * @param $callable
     * @return Result
     * @throws Throwable
     */
    public static function execute($experimentName, $callable)
    {
        $instance = new self;
        call_user_func_array($callable, array($instance));
        return $instance->run($experimentName);
    }

    /**
     * @param callable $callable
     * @param null $params
     * @return $this
     */
    public function control(callable $callable, $params = null)
    {
        $this->control       = $callable;
        $this->controlParams = $params;

        return $this;
    }

    /**
     * @param callable $callable
     * @param null $params
     * @return $this
     */
    public function candidate(callable $callable, $params = null)
    {
        $this->candidate       = $callable;
        $this->candidateParams = $params;

        return $this;
    }

    /**
     * @param callable $callable
     * @return $this
     */
    public function comparator(callable $callable)
    {
        $this->comparator = $callable;
        return $this;
    }

    /**
     * Runs the control and candidate code, returning the control results.
     * Note: Throws a runtime exception if either codepath is missing.
     *
     * @param string $experimentName
     * @return Result
     * @throws Throwable
     */
    public function run($experimentName)
    {
        if (empty($this->control) || empty($this->candidate)) {
            throw new RuntimeException('Missing control or candidate callable');
        }

        // randomise order in which the two paths are called.
        if (random_int(0, 1) === 1) {
            $controlResult   = $this->observeCallable($experimentName, 'control', $this->control, $this->controlParams);
            $candidateResult = $this->observeCallable($experimentName, 'candidate', $this->candidate, $this->candidateParams);
        } else {
            $candidateResult = $this->observeCallable($experimentName, 'candidate', $this->candidate, $this->candidateParams);
            $controlResult   = $this->observeCallable($experimentName, 'control', $this->control, $this->controlParams);
        }

        $comparisonResult = $this->compare($controlResult->getResult(), $candidateResult->getResult());

        $this->logResults($controlResult, $comparisonResult);
        
        if ($controlResult->isException()) {
            throw $controlResult->getException();
        }

        return $controlResult->getValue();
    }

    /**
     * @param $experimentName
     * @param $trial
     * @param  $callable
     * @param array $params
     * @return Result
     */
    protected function observeCallable($experimentName, $trial, &$callable, $params)
    {
        $result = $exception = null;

        $start = microtime(true);
        try {
            $result = call_user_func_array($callable, $params);
        } catch(\Exception $e) {
            $exception = $e;
        }
        $duration = microtime(true) - $start;

        return new Result($experimentName, $trial, $result, $duration, $exception);
    }

    /**
     * @param  Result $a
     * @param  Result $b
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
     * @todo: Interface and extract this, make dependency optional requirement in composer.json.
     * @todo is this slow or a terrible idea, just assert ===?
     *
     * @param  mixed $a
     * @param  mixed $b
     * @return bool
     */
    protected function defaultCompare($a, $b)
    {
        // TODO(philipmuir): move compare functions to own interface and have phpunit comparisions as an option
//        $factory = new Factory;
//        $comparator = $factory->getComparatorFor($a, $b);
//
//        try {
//            return $comparator->assertEquals($a, $b);
//        }  catch (ComparisonFailure $failure) {
//            return false;
//        }
        return $a == $b;
    }

    protected function logResults($controlResult, $candidateResult)
    {
        //todo: syslog errors/statsd counts
    }
}