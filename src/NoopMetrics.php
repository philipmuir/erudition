<?php declare(strict_types=1);

namespace Erudition;

class NoopMetrics implements MetricsCollector
{
    const PREFIX = 'experiment';

    /**
     * @param ExperimentResults $results
     */
    public function collectExperimentTelemetry(ExperimentResults $results)
    {
    }
}
