<?php

declare(strict_types=1);

namespace Aura\Storage;

use Aura\Contracts\StorageInterface;
use Aura\DTO\MetricData;
use Aura\DTO\MetricType;
use Illuminate\Support\Collection;

class NullStorage implements StorageInterface
{
    public function store(MetricData $metric): void
    {
    }

    public function storeBatch(array $metrics): void
    {
    }

    public function retrieve(MetricType $type, array $filters = []): Collection
    {
        return collect();
    }

    public function prune(\DateTimeInterface $before): void
    {
    }
}
