<?php

declare(strict_types=1);

namespace Aura\Storage;

use Aura\Contracts\StorageInterface;
use Aura\DTO\MetricData;
use Aura\DTO\MetricType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

class RedisStorage implements StorageInterface
{
    protected string $key = 'aura:metrics';

    public function store(MetricData $metric): void
    {
        Redis::rpush($this->key, json_encode($metric->toArray()));
    }

    public function storeBatch(array $metrics): void
    {
        $payloads = array_map(fn(MetricData $m) => json_encode($m->toArray()), $metrics);
        
        Redis::rpush($this->key, ...$payloads);
    }

    public function retrieve(MetricType $type, array $filters = []): Collection
    {
        $data = Redis::lrange($this->key, -100, -1);
        
        return collect($data)
            ->map(fn($item) => json_decode($item, true))
            ->where('type', $type->value)
            ->map(fn($item) => new MetricData(
                type: MetricType::from($item['type']),
                value: (float) $item['value'],
                tags: $item['tags'] ?? [],
                traceId: $item['trace_id'] ?? null,
                timestamp: (float) $item['timestamp']
            ))
            ->values();
    }

    public function prune(\DateTimeInterface $before): void
    {
        Redis::ltrim($this->key, -1000, -1);
    }
}
