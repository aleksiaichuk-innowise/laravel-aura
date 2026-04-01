<?php

declare(strict_types=1);

namespace Aura\Insights;

use Aura\Contracts\InsightInterface;
use Aura\DTO\MetricData;
use Aura\DTO\MetricType;
use Illuminate\Support\Collection;

class DatabaseInsight implements InsightInterface
{
    protected int $duplicateThreshold = 2;
    protected int $nPlusOneThreshold = 5;

    /**
     * @throws \JsonException
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
            
            $sqlHash = md5($sql);
            $fullHash = md5(sprintf("%s%s", $sql, json_encode($bindings, JSON_THROW_ON_ERROR)));

            $queryHashes[$sqlHash] = ($queryHashes[$sqlHash] ?? 0) + 1;
            $fullHashes[$fullHash] = ($fullHashes[$fullHash] ?? 0) + 1;

            if ($queryHashes[$sqlHash] >= $this->nPlusOneThreshold && !isset($detectedNPlusOne[$sqlHash])) {
                $insights->push(new MetricData(
                    type: MetricType::DATABASE_QUERY,
                    value: $queryHashes[$sqlHash],
                    tags: [
                        'sql' => $sql,
                        'insight' => 'Possible N+1 problem detected',
                        'severity' => 'warning'
                    ]
                ));
                $detectedNPlusOne[$sqlHash] = true;
            }

            if ($fullHashes[$fullHash] >= $this->duplicateThreshold && !isset($detectedDuplicates[$fullHash])) {
                $insights->push(new MetricData(
                    type: MetricType::DATABASE_QUERY,
                    value: $fullHashes[$fullHash],
                    tags: [
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
