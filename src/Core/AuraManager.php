<?php

declare(strict_types=1);

namespace Aura\Core;

use Aura\Contracts\StorageInterface;
use Aura\DTO\Metrics\MetricData;
use Aura\DTO\Metrics\MetricType;

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
        // Automatically inject current trace_id if missing
        $traceId = $metric->traceId ?? $this->tracker->getTraceId();

        $metric = new MetricData(
            type: $metric->type,
            value: $metric->value,
            tags: $metric->tags,
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

        $toStore = $processedMetrics
            ->filter(fn (MetricData $m) => $this->shouldStore($m))
            ->map(function (MetricData $m) {
                // Mask sensitive data in tags right before storing
                return new MetricData(
                    type: $m->type,
                    value: $m->value,
                    tags: $this->masker->mask($m->tags),
                    traceId: $m->traceId,
                    timestamp: $m->timestamp
                );
            });

        if ($toStore->isNotEmpty()) {
            $this->storage->storeBatch($toStore->all());
        }
        
        $this->metrics = [];
    }

    /**
     * @param MetricData $metric
     * @return bool
     */
    protected function shouldStore(MetricData $metric): bool
    {
        if ($metric->type === MetricType::INSIGHT) {
            return true;
        }

        if (in_array($metric->type, [
            MetricType::DATABASE_QUERY,
            MetricType::EXTERNAL_HTTP_REQUEST,
            MetricType::REQUEST_DURATION
        ], true)) {
            return $metric->tags['slow'] ?? false;
        }

        return true;
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
