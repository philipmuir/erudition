<?php
namespace Erudition;

use Throwable;

class Result
{
    /** @var  mixed */
    protected $value;

    /** @var  float */
    protected $duration;

    /** @var  null|Throwable */
    protected $exception;

    /** @var string */
    private $experimentName;

    /** @var string */
    private $trialName;

    /**
     * Result constructor.
     * @param string $experimentName
     * @param string $trialName
     * @param $value
     * @param float $duration
     * @param Throwable $exception
     */
    public function __construct(
        string $experimentName,
        string $trialName,
        $value,
        float $duration,
        Throwable $exception = null)
    {
        $this->experimentName = $experimentName;
        $this->trialName = $trialName;
        $this->value = $value;
        $this->duration = $duration;
        $this->exception = $exception;
    }

    /**
     * Returns the exception if set, otherwise the value.
     * @return Throwable|mixed|null
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
     * @return Throwable|null
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
        return ($this->exception instanceof Throwable);
    }
}