<?php

declare(strict_types=1);

namespace Aura\Contracts;

use Aura\DTO\Metrics\MetricData;
use Aura\DTO\Metrics\MetricType;
use Illuminate\Support\Collection;

interface StorageInterface
{
    public function store(MetricData $metric): void;

    /**
     * @param array<int, MetricData> $metrics
     */
    public function storeBatch(array $metrics): void;

    /**
     * @param array<string, mixed> $filters
     * @return Collection<int, MetricData>
     */
    public function retrieve(MetricType $type, array $filters = []): Collection;

    public function prune(\DateTimeInterface $before): void;
}
