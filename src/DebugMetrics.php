<?php

namespace Erudition;

class DebugMetrics implements MetricsCollector
{
    const PREFIX = 'erudition.experiment';

    /**
     * @param ExperimentResults $results
     */
    public function collectExperimentTelemetry(ExperimentResults $results)
    {

        print_r(implode('.', [
            self::PREFIX,
            $this->normaliseEperimentName($results->getName()),
            $this->normaliseEperimentName($results->successAsString()),
        ]));
        echo PHP_EOL;

        print_r(implode('.', [
                self::PREFIX,
                $this->normaliseEperimentName($results->getName()),
                $this->normaliseEperimentName($results->getControl()->getTrialName()),
            ]) . ' ' . $results->getControl()->getDuration() * 1000 . 's');
        echo PHP_EOL;

        print_r(implode('.', [
                self::PREFIX,
                $this->normaliseEperimentName($results->getName()),
                $this->normaliseEperimentName($results->getCandidate()->getTrialName()),
            ]) . ' ' . $results->getCandidate()->getDuration() * 1000 . 's');
        echo PHP_EOL;
    }

    /**
     * @param string $name
     * @return string
     */
    private function normaliseEperimentName(string $name): string
    {
        return preg_replace("/[\W_]+/", '', $name);
    }
}
