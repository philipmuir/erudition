<?php
namespace Erudition;

use SebastianBergmann\Comparator\Factory;
use SebastianBergmann\Comparator\ComparisonFailure;


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
     * @return ParallelCodePathResult
     */
    public static function execute($experimentName, $callable)
    {
        $instance = new self;
        call_user_func_array($callable, array($instance));
        return $instance->run($experimentName);
    }

    public function control($callable, $params = null)
    {
        $this->control       = $callable;
        $this->controlParams = $params;

        return $this;
    }

    public function candidate($callable, $params = null)
    {
        $this->candidate       = $callable;
        $this->candidateParams = $params;

        return $this;
    }

    public function comparator($callable)
    {
        $this->comparator = $callable;
        return $this;
    }

    /**
     * Runs the control and candidate code, returning the control results.
     * Note: Throws a runtime exception if either codepath is missing.
     *
     * @param string $experimentName
     * @return ParallelCodePathResult
     * @throws \Exception
     */
    public function run($experimentName)
    {
        if (empty($this->control) || empty($this->candidate)) {
            throw new \RuntimeException('Missing control or candidate callable');
        }

        $controlResult   = $this->observeCallable($this->control, $this->controlParams);
        $candidateResult = $this->observeCallable($this->candidate, $this->candidateParams);

        $comparisonResult = $this->compare($controlResult->getResult(), $candidateResult->getResult());
        if (!$comparisonResult) {
            $this->logResults($controlResult, $comparisonResult);
        }
        
        if ($controlResult->isException()) {
            throw $controlResult->getException();
        }

        return $controlResult->getValue();
    }

    /**
     * @param  $callable
     * @param  array $params
     * @return ParallelCodePathResult
     */
    protected function observeCallable(&$callable, $params)
    {
        $result = $exception = null;

        $start = microtime(true);
        try {
            $result = call_user_func_array($callable, $params);
        } catch(\Exception $e) {
            $exception = $e;
        }
        $duration = microtime(true) - $start;

        return new ParallelCodePathResult($result, $duration, $exception);
    }

    /**
     * @param  ParallelCodePathResult $a
     * @param  ParallelCodePathResult $b
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
        $factory = new Factory;
        $comparator = $factory->getComparatorFor($a, $b);

        try {
            return $comparator->assertEquals($a, $b);
        }  catch (ComparisonFailure $failure) {
            return false;
        }
    }

    protected function logResults($controlResult, $candidateResult)
    {
        //todo: syslog errors/statsd counts
    }
}