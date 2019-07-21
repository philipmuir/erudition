<?php

namespace Erudition;

class DebugMetrics implements MetricsCollector
{
    const PREFIX = 'experiment';

    /**
     * @param ExperimentResults $results
     */
    public function collectExperimentMetrics(ExperimentResults $results)
    {

        print_r(implode('.', [
            self::PREFIX,
            $results->getName(),
            $results->successAsString()
        ]));
        echo PHP_EOL;

        print_r(implode('.', [
                self::PREFIX,
                $results->getName(),
                $results->getControl()->getTrialName()
            ]) . ' ' . $results->getControl()->getDuration() * 1000 . 's');
        echo PHP_EOL;

        print_r(implode('.', [
                self::PREFIX,
                $results->getName(),
                $results->getTrial()->getTrialName()
            ]) . ' ' . $results->getTrial()->getDuration() * 1000 . 's');
        echo PHP_EOL;
    }
}
