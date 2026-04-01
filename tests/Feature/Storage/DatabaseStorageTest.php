<?php

namespace Aura\Tests\Feature\Storage;

use Aura\Storage\DatabaseStorage;
use Aura\DTO\MetricData;
use Aura\DTO\MetricType;
use Aura\Tests\TestCase;
use Illuminate\Support\Facades\DB;

class DatabaseStorageTest extends TestCase
{
    public function test_it_stores_single_metric()
    {
        $storage = new DatabaseStorage();
        $metric = new MetricData(MetricType::MEMORY_USAGE, 128.5, ['unit' => 'MB']);

        $storage->store($metric);

        $this->assertDatabaseHas('aura_metrics', [
            'type' => MetricType::MEMORY_USAGE->value,
            'value' => 128.5,
            'tags' => '{"unit":"MB"}',
        ]);
    }

    public function test_it_stores_batch_metrics()
    {
        $storage = new DatabaseStorage();
        $metrics = [
            new MetricData(MetricType::MEMORY_USAGE, 10),
            new MetricData(MetricType::DATABASE_QUERY, 20),
        ];

        $storage->storeBatch($metrics);

        $this->assertDatabaseCount('aura_metrics', 2);
    }
}
