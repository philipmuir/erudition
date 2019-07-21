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

    /** @var ExperimentResults|null */
    private $experimentResults = null;

    /** @var MetricsCollector */
    private $metricsCollector;

    /**
     * Cunt
     *
     * @param string $experimentName Loggable experiment name
     * @param callable $callable
     *
     * @param MetricsCollector|null $m
     * @return mixed
     * @throws Throwable
     */
    public static function execute(string $experimentName, callable $callable, MetricsCollector $m = null)
    {
        $instance = new self($experimentName, null, $m);
        call_user_func_array($callable, [$instance]);
        return $instance->run();
    }

    /**
     * Experiment constructor.
     * @param string $experimentName
     * @param callable $comparator
     * @param MetricsCollector|null $metricsCollector
     */
    public function __construct(
        string $experimentName,
        callable $comparator = null,
        MetricsCollector $metricsCollector = null
    ) {
        $this->experimentName   = $experimentName;
        $this->comparator       = $comparator;
        $this->metricsCollector = $metricsCollector ?? new NoopMetrics();
    }

    /**
     * Runs the control and candidate code, returning the control results.
     * Note: Throws a runtime exception if either codepath is missing.
     *
     * @return mixed
     * @throws Throwable
     */
    public function run()
    {
        if ($this->experimentResults) {
            throw new RuntimeException('Experiment has already been run');
        }

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

        // compare & log experiment run
        $this->experimentResults = $this->compare($controlResult, $candidateResult);
        $this->metricsCollector->collectExperimentTelemetry($this->experimentResults);

        // return control result or throw exception.
        if ($controlResult->isException()) {
            throw $controlResult->getException();
        }

        return $controlResult->getValue();
    }

    /**
     * @param string $trialName
     * @param callable $callable
     * @param array $params
     * @return TrialResult
     */
    private function runTrial(string $trialName, callable $callable, array $params): TrialResult
    {
        $r = $this->observeCallable(
            $this->experimentName,
            $trialName,
            $callable,
            $params
        );

        syslog(LOG_WARNING, $r->getExperimentName());

        return $r;
    }

    /**
     * A
     *
     * @param string   $experimentName a
     * @param string   $trial          a
     * @param callable $callable       a
     * @param array    $params         a
     *
     * @return TrialResult
     */
    protected function observeCallable(string $experimentName, string $trial, callable &$callable, array $params)
    {
        $result = $exception = null;

        $start = microtime(true);
        try {
            $result = call_user_func_array($callable, $params);
        } catch (Exception $e) {
            $exception = $e;
        }
        $duration = microtime(true) - $start;

        return new TrialResult($experimentName, $trial, $result, $duration, $exception);
    }

    /**
     * A
     *
     * @param TrialResult $control
     * @param TrialResult $trial
     *
     * @return ExperimentResults
     */
    protected function compare(TrialResult $control, TrialResult $trial): ExperimentResults
    {
        if (is_callable($this->comparator)) {
            return call_user_func_array($this->comparator, [$control->getResult(), $trial->getResult()]);
        }

        return $this->defaultCompare($control, $trial);
    }

    /**
     * Defaults compare function using assertEquals from PHPUnit.
     *
     * @param TrialResult $control
     * @param TrialResult $trial
     * @return bool
     */
    protected function defaultCompare(TrialResult $control, TrialResult $trial): ExperimentResults
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
        return new ExperimentResults(
            $control->getResult() === $trial->getResult(),
            $control,
            $trial
        );
    }

    /**
     * A
     *
     * @param callable $callable Call me
     * @param array $params Params
     *
     * @return $this
     */
    public function control(callable $callable, $params = [])
    {
        $this->control = $callable;
        $this->controlParams = $params;

        return $this;
    }

    /**
     * B
     *
     * @param callable $callable callable
     * @param array $params function params
     *
     * @return $this
     */
    public function candidate(callable $callable, $params = [])
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
