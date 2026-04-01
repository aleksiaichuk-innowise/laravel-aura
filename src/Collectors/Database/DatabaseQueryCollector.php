<?php

declare(strict_types=1);

namespace Aura\Collectors\Database;

use Aura\Contracts\CollectorInterface;
use Aura\Core\AuraManager;
use Aura\DTO\MetricData;
use Aura\DTO\MetricType;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Event;

class DatabaseQueryCollector implements CollectorInterface
{
    /**
     * @param AuraManager $manager
     */
    public function __construct(
        protected AuraManager $manager
    ) {
    }

    /**
     * @return void
     */
    public function register(): void
    {
        Event::listen(QueryExecuted::class, function (QueryExecuted $event) {
            $this->collect($event);
        });
    }

    /**
     *
     * Always record queries to allow InsightEngine to analyze patterns (N+1, duplicates)
     * In production, we might want to sample this or only record if certain conditions are met
     * For this showcase, we record all to demonstrate the Insight Engine.
     *
     * @param QueryExecuted $event
     * @return void
     */
    protected function collect(QueryExecuted $event): void
    {
        $this->manager->record(new MetricData(
            type: MetricType::DATABASE_QUERY,
            value: $event->time,
            tags: [
                'sql' => $event->sql,
                'bindings' => $event->bindings,
                'connection' => $event->connectionName,
                'slow' => $event->time >= config('aura.slow_query_threshold', 10),
            ]
        ));
    }
}
