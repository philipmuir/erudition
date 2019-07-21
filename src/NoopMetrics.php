<?php

namespace Erudition;

class NoopMetrics implements MetricsCollector
{
    const PREFIX = 'experiment';

    /**
     * @param ExperimentResults $results
     */
    public function collectExperimentMetrics(ExperimentResults $results)
    {
    }
}
