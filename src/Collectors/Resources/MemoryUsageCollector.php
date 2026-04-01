<?php

declare(strict_types=1);

namespace Aura\Collectors\Resources;

use Aura\Contracts\CollectorInterface;
use Aura\Core\AuraManager;
use Aura\DTO\MetricData;
use Aura\DTO\MetricType;
use Illuminate\Contracts\Foundation\Application;

class MemoryUsageCollector implements CollectorInterface
{
    public function __construct(
        protected AuraManager $manager,
        protected Application $app
    ) {
    }

    public function register(): void
    {
        $this->app->terminating(function () {
            $this->collect();
        });
    }

    public function collect(): void
    {
        $memory = memory_get_peak_usage(true) / 1024 / 1024;

        $this->manager->record(new MetricData(
            type: MetricType::MEMORY_USAGE,
            value: $memory,
            tags: [
                'unit' => 'MB',
            ]
        ));
    }
}
