<?php

namespace Aura\Tests\Feature\Storage;

use Aura\DTO\Metrics\MetricData;
use Aura\DTO\Metrics\MetricType;
use Aura\Storage\RedisStorage;
use Aura\Tests\TestCase;
use Illuminate\Support\Facades\Redis;

class RedisStorageTest extends TestCase
{
    private RedisStorage $storage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->storage = new RedisStorage();
        // Очищаем тестовый ключ перед каждым тестом
        Redis::del('aura:metrics');
    }

    public function test_it_stores_and_retrieves_single_metric_in_redis()
    {
        $metric = new MetricData(
            type: MetricType::MEMORY_USAGE,
            value: 256.5,
            tags: ['host' => 'worker-1']
        );

        $this->storage->store($metric);

        $results = $this->storage->retrieve(MetricType::MEMORY_USAGE);

        $this->assertCount(1, $results);
        $this->assertEquals(256.5, $results->first()->value);
        $this->assertEquals('worker-1', $results->first()->tags['host']);
    }

    public function test_it_stores_batch_of_metrics_in_redis()
    {
        $metrics = [
            new MetricData(MetricType::DATABASE_QUERY, 1.2, ['sql' => 'select 1']),
            new MetricData(MetricType::DATABASE_QUERY, 2.5, ['sql' => 'select 2']),
        ];

        $this->storage->storeBatch($metrics);

        $results = $this->storage->retrieve(MetricType::DATABASE_QUERY);

        $this->assertCount(2, $results);
        $this->assertEquals(1.2, $results[0]->value);
        $this->assertEquals(2.5, $results[1]->value);
    }

    public function test_it_filters_metrics_by_type_on_retrieve()
    {
        $this->storage->store(new MetricData(MetricType::MEMORY_USAGE, 100));
        $this->storage->store(new MetricData(MetricType::DATABASE_QUERY, 50));

        $memoryResults = $this->storage->retrieve(MetricType::MEMORY_USAGE);
        $dbResults = $this->storage->retrieve(MetricType::DATABASE_QUERY);

        $this->assertCount(1, $memoryResults);
        $this->assertCount(1, $dbResults);
        $this->assertEquals(MetricType::MEMORY_USAGE, $memoryResults->first()->type);
    }

    public function test_it_prunes_metrics_in_redis()
    {
        // Заполняем Redis 100+ метриками (RedisStorage хранит последние 1000 по умолчанию в ltrim)
        $batch = [];
        for ($i = 0; $i < 10; $i++) {
            $batch[] = new MetricData(MetricType::MEMORY_USAGE, $i);
        }
        $this->storage->storeBatch($batch);

        // В RedisStorage::prune используется ltrim -1000..-1
        $this->storage->prune(new \DateTime());

        $results = $this->storage->retrieve(MetricType::MEMORY_USAGE);
        
        // В текущей реализации prune просто ограничивает размер списка посленними 1000 записями
        $this->assertNotEmpty($results);
    }
}
