<?php

namespace Aura\Tests\Feature;

use Aura\Aura;
use Aura\DTO\MetricData;
use Aura\DTO\MetricType;
use Aura\Contracts\StorageInterface;
use Aura\Tests\TestCase;

class ComprehensiveDashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Aura::auth(static fn() => true);
    }

    public function test_it_renders_dashboard_with_all_metrics_populated(): void
    {
        $storage = app(StorageInterface::class);

        $storage->store(new MetricData(MetricType::DATABASE_QUERY, 100, ['slow' => true]));
        $storage->store(new MetricData(MetricType::DATABASE_QUERY, 10, ['insight' => 'Duplicate query']));
        $storage->store(new MetricData(MetricType::EXTERNAL_HTTP_REQUEST, 2000, ['slow' => true]));
        $storage->store(new MetricData(MetricType::REQUEST_DURATION, 50, []));
        $storage->store(new MetricData(MetricType::MEMORY_USAGE, 128, []));
        $storage->store(new MetricData(MetricType::CACHE_OPERATION, 1, ['operation' => 'hit']));
        $storage->store(new MetricData(MetricType::JOB_EXECUTION, 1, ['status' => 'processed']));

        $response = $this->get('/aura');

        $response->assertStatus(200);
        $response->assertViewHas('slowQueries');
        $response->assertViewHas('insights');
        $response->assertViewHas('slowHttp');
    }
}
