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

    public function process(Collection $metrics): Collection
    {
        $queries = $metrics->filter(fn(MetricData $m) => $m->type === MetricType::DATABASE_QUERY);
        
        if ($queries->isEmpty()) {
            return $metrics;
        }

        $insights = new Collection();
        $queryHashes = [];
        $fullHashes = [];

        foreach ($queries as $metric) {
            $sql = $metric->tags['sql'] ?? '';
            $bindings = $metric->tags['bindings'] ?? [];
            
            $sqlHash = md5($sql);
            $fullHash = md5($sql . json_encode($bindings));

            $queryHashes[$sqlHash] = ($queryHashes[$sqlHash] ?? 0) + 1;
            $fullHashes[$fullHash] = ($fullHashes[$fullHash] ?? 0) + 1;

            if ($queryHashes[$sqlHash] === $this->nPlusOneThreshold) {
                $insights->push(new MetricData(
                    type: MetricType::DATABASE_QUERY,
                    value: $queryHashes[$sqlHash],
                    tags: [
                        'sql' => $sql,
                        'insight' => 'Possible N+1 problem detected',
                        'severity' => 'warning'
                    ]
                ));
            }

            if ($fullHashes[$fullHash] === $this->duplicateThreshold) {
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
            }
        }

        return $metrics->merge($insights);
    }
}
