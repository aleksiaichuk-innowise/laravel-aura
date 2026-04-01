<?php

declare(strict_types=1);

namespace Aura\Contracts;

use Aura\DTO\Metrics\MetricData;
use Illuminate\Support\Collection;

interface InsightInterface
{
    /**
     * Analyze a collection of metrics and return new insights or modified metrics.
     *
     * @param Collection<int, MetricData> $metrics
     * @return Collection<int, MetricData>
     */
    public function process(Collection $metrics): Collection;
}
