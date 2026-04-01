<?php

declare(strict_types=1);

namespace Aura\Contracts;

use Illuminate\Support\Collection;

interface InsightInterface
{
    /**
     * Analyze a collection of metrics and return new insights or modified metrics.
     *
     * @param Collection<\Aura\DTO\MetricData> $metrics
     * @return Collection<\Aura\DTO\MetricData>
     */
    public function process(Collection $metrics): Collection;
}
