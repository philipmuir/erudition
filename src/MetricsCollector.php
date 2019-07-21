<?php

namespace Erudition;

/**
 * Interface MetricsCollector
 *
 * @package Erudition
 */
interface MetricsCollector
{
    public function collectExperimentMetrics(ExperimentResults $results);
}
