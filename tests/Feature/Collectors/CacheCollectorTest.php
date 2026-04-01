<?php

namespace Aura\Tests\Feature\Collectors;

use Aura\Core\AuraManager;
use Aura\DTO\MetricType;
use Aura\Tests\TestCase;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Support\Facades\Event;

class CacheCollectorTest extends TestCase
{
    public function test_it_collects_cache_hit_events()
    {
        $manager = app(AuraManager::class);
        
        // Эмулируем событие попадания в кеш
        Event::dispatch(new CacheHit('test_1', 'value', []));

        $metrics = collect($manager->getMetrics());
        $cacheMetrics = $metrics->filter(fn($m) => $m->type === MetricType::CACHE_OPERATION);

        $this->assertCount(1, $cacheMetrics);
        $this->assertEquals('hit', $cacheMetrics->first()->tags['operation']);
        $this->assertEquals('********', $cacheMetrics->first()->tags['key']);
    }

    public function test_it_collects_cache_miss_events()
    {
        $manager = app(AuraManager::class);
        
        // Эмулируем событие промаха в кеше
        Event::dispatch(new CacheMissed('test_2', []));

        $metrics = collect($manager->getMetrics());
        $cacheMetrics = $metrics->filter(fn($m) => $m->type === MetricType::CACHE_OPERATION);

        $this->assertCount(1, $cacheMetrics);
        $this->assertEquals('miss', $cacheMetrics->first()->tags['operation']);
    }

    public function test_it_collects_cache_write_events()
    {
        $manager = app(AuraManager::class);
        
        // Эмулируем событие записи в кеш
        Event::dispatch(new KeyWritten('test_3', 'value', 60));

        $metrics = collect($manager->getMetrics());
        $cacheMetrics = $metrics->filter(fn($m) => $m->type === MetricType::CACHE_OPERATION);

        $this->assertCount(1, $cacheMetrics);
        $this->assertEquals('write', $cacheMetrics->first()->tags['operation']);
    }
}
