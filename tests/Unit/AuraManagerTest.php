<?php

namespace Aura\Tests\Unit;

use Aura\Contracts\StorageInterface;
use Aura\Core\AuraManager;
use Aura\Core\DataMasker;
use Aura\Core\InsightEngine;
use Aura\Core\Tracker;
use Aura\DTO\Metrics\MetricData;
use Aura\DTO\Metrics\MetricType;
use Aura\Tests\TestCase;
use Mockery;

class AuraManagerTest extends TestCase
{
    public function test_it_processes_and_filters_metrics()
    {
        $storage = Mockery::mock(StorageInterface::class);
        $masker = new DataMasker();
        $tracker = new Tracker();
        $insightEngine = new InsightEngine(); // Real engine to test processing loop

        $manager = new AuraManager($storage, $insightEngine, $tracker, $masker);

        $metric = new MetricData(
            type: MetricType::DATABASE_QUERY,
            value: 200,
            tags: ['sql' => 'select * from users', 'slow' => true]
        );

        $manager->record($metric);

        // Мы ожидаем, что при сохранении будет:
        // 1. Проброшен Trace ID.
        // 2. Вызван storeBatch.
        
        $storage->shouldReceive('storeBatch')
            ->once()
            ->with(Mockery::on(function ($metrics) {
                return count($metrics) === 1 && $metrics[0]->traceId !== null;
            }));

        $manager->flush();
    }

    public function test_it_skips_recording_if_not_slow_and_no_insights()
    {
        $storage = Mockery::mock(StorageInterface::class);
        $manager = new AuraManager($storage, new InsightEngine(), new Tracker(), new DataMasker());

        $metric = new MetricData(
            type: MetricType::DATABASE_QUERY,
            value: 1, // Очень быстрый запрос
            tags: ['sql' => 'select 1', 'slow' => false]
        );

        $manager->record($metric);

        $storage->shouldNotReceive('storeBatch');

        $manager->flush();
    }
}
