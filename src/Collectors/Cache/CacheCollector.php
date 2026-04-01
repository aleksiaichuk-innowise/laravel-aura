<?php

declare(strict_types=1);

namespace Aura\Collectors\Cache;

use Aura\Contracts\CollectorInterface;
use Aura\Core\AuraManager;
use Aura\DTO\Metrics\MetricData;
use Aura\DTO\Metrics\MetricType;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Support\Facades\Event;

class CacheCollector implements CollectorInterface
{
    /**
     * @param AuraManager $manager
     */
    public function __construct(
        protected AuraManager $manager
    ) {
    }

    public function register(): void
    {
        Event::listen(CacheHit::class, [$this, 'handleCacheHit']);
        Event::listen(CacheMissed::class, [$this, 'handleCacheMiss']);
        Event::listen(KeyWritten::class, [$this, 'handleKeyWritten']);
    }

    public function handleCacheHit($event): void
    {
        $this->collect($event, 'hit');
    }

    public function handleCacheMiss($event): void
    {
        $this->collect($event, 'miss');
    }

    public function handleKeyWritten($event): void
    {
        $this->collect($event, 'write');
    }

    /**
     * @param $event
     * @param string $operation
     * @return void
     */
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
