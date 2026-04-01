<?php

declare(strict_types=1);

namespace Aura\Collectors\Cache;

use Aura\Contracts\CollectorInterface;
use Aura\Core\AuraManager;
use Aura\DTO\MetricData;
use Aura\DTO\MetricType;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Support\Facades\Event;

class CacheCollector implements CollectorInterface
{
    public function __construct(
        protected AuraManager $manager
    ) {
    }

    public function register(): void
    {
        Event::listen(CacheHit::class, fn($event) => $this->collect($event, 'hit'));
        Event::listen(CacheMissed::class, fn($event) => $this->collect($event, 'miss'));
        Event::listen(KeyWritten::class, fn($event) => $this->collect($event, 'write'));
    }

    protected function collect($event, string $operation): void
    {
        $this->manager->record(new MetricData(
            type: MetricType::CACHE_OPERATION,
            value: 1,
            tags: [
                'key' => $event->key,
                'operation' => $operation,
                'tags' => $event->tags ?? [],
            ]
        ));
    }
}
