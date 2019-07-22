<?php declare(strict_types=1);

namespace Erudition;

/**
 * Interface MetricsCollector
 *
 * @package Erudition
 */
interface MetricsCollector
{
    public function collectExperimentTelemetry(ExperimentResults $results);
}
