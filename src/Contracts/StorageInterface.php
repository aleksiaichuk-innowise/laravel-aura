<?php

declare(strict_types=1);

namespace Aura\Contracts;

use Aura\DTO\MetricData;
use Aura\DTO\MetricType;
use Illuminate\Support\Collection;

interface StorageInterface
{
    public function store(MetricData $metric): void;

    public function storeBatch(array $metrics): void;

    public function retrieve(MetricType $type, array $filters = []): Collection;

    public function prune(\DateTimeInterface $before): void;
}
