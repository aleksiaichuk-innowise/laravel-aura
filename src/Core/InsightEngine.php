<?php

declare(strict_types=1);

namespace Aura\Core;

use Aura\Contracts\InsightInterface;
use Illuminate\Support\Collection;

class InsightEngine
{
    /** @var InsightInterface[] */
    protected array $insights = [];

    public function __construct(array $insights = [])
    {
        foreach ($insights as $insight) {
            $this->addInsight($insight);
        }
    }

    public function addInsight(InsightInterface $insight): void
    {
        $this->insights[] = $insight;
    }

    /**
     * Run all registered insights on the metrics collection.
     *
     * @param Collection $metrics
     * @return Collection
     */
    public function analyze(Collection $metrics): Collection
    {
        $results = $metrics;

        foreach ($this->insights as $insight) {
            $results = $insight->process($results);
        }

        return $results;
    }
}
