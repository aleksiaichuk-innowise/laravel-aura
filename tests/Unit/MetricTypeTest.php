<?php

namespace Aura\Tests\Unit;

use Aura\DTO\Metrics\MetricType;
use Aura\Tests\TestCase;

class MetricTypeTest extends TestCase
{
    public function test_it_has_all_expected_cases()
    {
        $cases = MetricType::cases();
        
        $this->assertContains(MetricType::DATABASE_QUERY, $cases);
        $this->assertContains(MetricType::REQUEST_DURATION, $cases);
        $this->assertContains(MetricType::MEMORY_USAGE, $cases);
        $this->assertContains(MetricType::EXTERNAL_HTTP_REQUEST, $cases);
        $this->assertContains(MetricType::CACHE_OPERATION, $cases);
        $this->assertContains(MetricType::JOB_EXECUTION, $cases);
    }

    public function test_it_returns_correct_values()
    {
        $this->assertEquals('database_query', MetricType::DATABASE_QUERY->value);
        $this->assertEquals('memory_usage', MetricType::MEMORY_USAGE->value);
    }
}
