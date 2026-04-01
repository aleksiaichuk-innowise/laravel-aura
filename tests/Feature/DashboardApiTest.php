<?php

namespace Aura\Tests\Feature;

use Aura\Aura;
use Aura\Contracts\StorageInterface;
use Aura\DTO\Metrics\MetricData;
use Aura\DTO\Metrics\MetricType;
use Aura\Tests\TestCase;

class DashboardApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Aura::auth(static fn() => true);
    }

    public function test_it_returns_metrics_json(): void
    {
        $storage = app(StorageInterface::class);
        $storage->store(new MetricData(MetricType::MEMORY_USAGE, 128));

        $response = $this->getJson('/aura/api/metrics/memory_usage');

        $response->assertStatus(200)
            ->assertJsonStructure(['metrics'])
            ->assertJsonPath('metrics.0.value', 128);
    }

    public function test_it_returns_400_for_invalid_metric_type(): void
    {
        $response = $this->getJson('/aura/api/metrics/invalid_type');

        $response->assertStatus(400)
            ->assertJson(['error' => 'Invalid metric type']);
    }
}
