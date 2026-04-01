<?php

declare(strict_types=1);

namespace Aura\Core;

use Aura\Contracts\StorageInterface;
use Aura\DTO\MetricData;
use Aura\DTO\MetricType;

class AuraManager
{
    /** @var MetricData[] */
    protected array $metrics = [];

    public function __construct(
        protected StorageInterface $storage,
        protected InsightEngine $insightEngine,
        protected Tracker $tracker,
        protected DataMasker $masker
    ) {
    }

    /**
     * @param MetricData $metric
     * @return void
     */
    public function record(MetricData $metric): void
    {
        // Mask sensitive data in tags
        $tags = $this->masker->mask($metric->tags);

        // Automatically inject current trace_id if missing
        $traceId = $metric->traceId ?? $this->tracker->getTraceId();

        $metric = new MetricData(
            type: $metric->type,
            value: $metric->value,
            tags: $tags,
            traceId: $traceId,
            timestamp: $metric->timestamp
        );

        $this->metrics[] = $metric;
    }

    /**
     * @return void
     */
    public function flush(): void
    {
        if (empty($this->metrics)) {
            return;
        }

        $metrics = collect($this->metrics);
        
        // Process metrics through Insight Engine
        $processedMetrics = $this->insightEngine->analyze($metrics);

        // Smart Filter: Keep only those that:
        // 1. Are marked as slow.
        // 2. Are insights (have 'insight' tag).
        // 3. We keep jobs, memory and cache by default for overall monitoring,
        // but restrict DB and HTTP to only problematic ones as requested.
        $toStore = $processedMetrics->filter(function (MetricData $m) {
            if ($m->type === MetricType::DATABASE_QUERY || $m->type === MetricType::EXTERNAL_HTTP_REQUEST) {
                return ($m->tags['slow'] ?? false) || isset($m->tags['insight']);
            }
            return true;
        });

        if ($toStore->isNotEmpty()) {
            $this->storage->storeBatch($toStore->all());
        }
        
        $this->metrics = [];
    }

    /**
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    /**
     * @return MetricData[]
     */
    public function getMetrics(): array
    {
        return $this->metrics;
    }
}
