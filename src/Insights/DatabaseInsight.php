<?php

declare(strict_types=1);

namespace Aura\Insights;

use Aura\Contracts\InsightInterface;
use Aura\DTO\Metrics\MetricData;
use Aura\DTO\Metrics\MetricType;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JsonException;

class DatabaseInsight implements InsightInterface
{
    protected int $duplicateThreshold = 2;
    protected int $nPlusOneThreshold = 5;

    /**
     * @throws JsonException
     */
    public function process(Collection $metrics): Collection
    {
        $queries = $metrics->filter(fn(MetricData $m) => $m->type === MetricType::DATABASE_QUERY);
        
        if ($queries->isEmpty()) {
            return $metrics;
        }

        $insights = new Collection();
        $queryHashes = [];
        $fullHashes = [];

        $detectedNPlusOne = [];
        $detectedDuplicates = [];

        foreach ($queries as $metric) {
            $sql = $metric->tags['sql'] ?? '';
            $bindings = $metric->tags['bindings'] ?? [];

            // Ignore internal Aura metrics table to prevent false positives
            if (Str::contains($sql, config('aura.metrics.table'))) {
                continue;
            }
            
            $sqlHash = md5($sql);
            $fullHash = md5(sprintf("%s%s", $sql, json_encode($bindings, JSON_THROW_ON_ERROR)));

            $queryHashes[$sqlHash] = ($queryHashes[$sqlHash] ?? 0) + 1;
            $fullHashes[$fullHash] = ($fullHashes[$fullHash] ?? 0) + 1;

            if ($queryHashes[$sqlHash] >= $this->nPlusOneThreshold && !isset($detectedNPlusOne[$sqlHash])) {
                $insights->push(new MetricData(
                    type: MetricType::INSIGHT,
                    value: $queryHashes[$sqlHash],
                    tags: [
                        'original_type' => MetricType::DATABASE_QUERY->value,
                        'sql' => $sql,
                        'insight' => 'Possible N+1 problem detected',
                        'severity' => 'warning'
                    ]
                ));
                $detectedNPlusOne[$sqlHash] = true;
            }

            if ($fullHashes[$fullHash] >= $this->duplicateThreshold && !isset($detectedDuplicates[$fullHash])) {
                $insights->push(new MetricData(
                    type: MetricType::INSIGHT,
                    value: $fullHashes[$fullHash],
                    tags: [
                        'original_type' => MetricType::DATABASE_QUERY->value,
                        'sql' => $sql,
                        'bindings' => $bindings,
                        'insight' => 'Duplicate query detected',
                        'severity' => 'info'
                    ]
                ));
                $detectedDuplicates[$fullHash] = true;
            }
        }

        return $metrics->merge($insights);
    }
}
