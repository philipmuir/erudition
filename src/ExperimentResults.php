<?php

namespace Erudition;

/**
 * Class ExperimentResults
 *
 * @package Erudition
 */
class ExperimentResults
{
    /**
     * @var bool
     */
    private $success;
    /**
     * @var TrialResult
     */
    private $control;
    /**
     * @var TrialResult
     */
    private $trial;

    /**
     * ExperimentResults constructor.
     * @param bool $success
     * @param TrialResult $control
     * @param TrialResult $trial
     */
    public function __construct(
        bool $success,
        TrialResult $control,
        TrialResult $trial
    ) {
        $this->success = $success;
        $this->control = $control;
        $this->trial = $trial;
    }

    public function getName(): string
    {
        return $this->control->getExperimentName();
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @return TrialResult
     */
    public function getControl(): TrialResult
    {
        return $this->control;
    }

    /**
     * @return TrialResult
     */
    public function getTrial(): TrialResult
    {
        return $this->trial;
    }

    /**
     * Returns either 'success' or 'failure'.
     *
     * @return string
     */
    public function successAsString(): string
    {
        return $this->isSuccess() ? 'success' : 'failure';
    }
}
