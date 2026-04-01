<?php

namespace Aura\Tests\Unit;

use Aura\DTO\MetricData;
use Aura\DTO\MetricType;
use Aura\Insights\DatabaseInsight;
use Aura\Tests\TestCase;
use Illuminate\Support\Collection;

class DatabaseInsightTest extends TestCase
{
    private DatabaseInsight $insight;

    protected function setUp(): void
    {
        parent::setUp();
        $this->insight = new DatabaseInsight();
    }

    public function test_it_detects_duplicate_queries()
    {
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

        $result = $this->insight->process($metrics);

        // Должен добавиться 1 инсайт о дубликате
        $insights = $result->filter(fn(MetricData $m) => isset($m->tags['insight']) && $m->tags['insight'] === 'Duplicate query detected');
        
        $this->assertCount(1, $insights);
    }

    public function test_it_detects_n_plus_one_queries()
    {
        $metrics = new Collection();
        
        // Добавляем 5 одинаковых SQL, но с разными биндингами
        for ($i = 1; $i <= 5; $i++) {
            $metrics->push(new MetricData(
                type: MetricType::DATABASE_QUERY,
                value: 5,
                tags: ['sql' => 'SELECT * FROM posts WHERE user_id = ?', 'bindings' => [$i]]
            ));
        }

        $result = $this->insight->process($metrics);

        // Должен добавиться 1 инсайт об N+1
        $insights = $result->filter(fn(MetricData $m) => isset($m->tags['insight']) && $m->tags['insight'] === 'Possible N+1 problem detected');
        
        $this->assertCount(1, $insights);
    }

    public function test_it_ignores_non_database_metrics()
    {
        $metrics = new Collection([
            new MetricData(
                type: MetricType::MEMORY_USAGE,
                value: 100,
                tags: []
            ),
        ]);

        $result = $this->insight->process($metrics);

        $this->assertCount(1, $result);
    }
}
