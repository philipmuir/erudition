<?php
namespace Erudition;

use SebastianBergmann\Comparator\Factory;
use SebastianBergmann\Comparator\ComparisonFailure;

class ParallelCodePathResult
{
    /** @var  mixed */
    protected $value;

    /** @var  int */
    protected $duration;

    /** @var  null|\Exception */
    protected $exception;

    public function __construct($value, $duration, $exception = null)
    {
        $this->value = $value;
        $this->duration = $duration;
        $this->exception = $exception;
    }

    /**
     * Returns the exception if set, otherwise the value.
     * @return \Exception|mixed|null
     */
    public function getResult()
    {
        if ($this->isException()) {
            return $this->getException();
        }

        return $this->getValue();
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @return \Exception|null
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @return bool
     */
    public function isException()
    {
        return ($this->exception instanceof \Exception);
    }
}