<?php

namespace Aura\Tests\Unit;

use Aura\Core\InsightEngine;
use Aura\DTO\MetricData;
use Aura\DTO\MetricType;
use Aura\Insights\DatabaseInsight;
use Aura\Tests\TestCase;
use Illuminate\Support\Collection;

class InsightEngineTest extends TestCase
{
    public function test_it_processes_metrics_through_all_insights()
    {
        $engine = new InsightEngine([
            new DatabaseInsight(),
        ]);

        $metrics = new Collection([
            new MetricData(
                type: MetricType::DATABASE_QUERY,
                value: 10,
                tags: ['sql' => 'SELECT * FROM users WHERE id = ?', 'bindings' => [1]]
            ),
            new MetricData(
                type: MetricType::DATABASE_QUERY,
                value: 15,
                tags: ['sql' => 'SELECT * FROM users WHERE id = ?', 'bindings' => [1]]
            ),
        ]);

        $result = $engine->analyze($metrics);

        // В результате должны быть оригинальные метрики + добавленный инсайт
        $this->assertCount(3, $result);
    }

    public function test_it_works_with_no_insights()
    {
        $engine = new InsightEngine([]);
        $metrics = new Collection([new MetricData(MetricType::MEMORY_USAGE, 100)]);

        $result = $engine->analyze($metrics);

        $this->assertCount(1, $result);
        $this->assertEquals($metrics, $result);
    }
}
