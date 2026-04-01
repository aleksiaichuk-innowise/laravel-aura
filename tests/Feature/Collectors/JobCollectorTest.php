<?php

namespace Aura\Tests\Feature\Collectors;

use Aura\Core\AuraManager;
use Aura\DTO\Metrics\MetricType;
use Aura\Tests\TestCase;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Event;
use Mockery;

class JobCollectorTest extends TestCase
{
    public function test_it_collects_processed_job_events()
    {
        $manager = app(AuraManager::class);
        
        // Создаем мок задания
        $job = Mockery::mock(\Illuminate\Contracts\Queue\Job::class);
        $job->shouldReceive('resolveName')->andReturn('ProcessPodcast');
        $job->shouldReceive('getQueue')->andReturn('default');

        // Используем реальный класс события с моком Job
        // В Laravel 10/11 конструктор JobProcessed: ($connectionName, $job)
        Event::dispatch(new JobProcessed('database', $job));

        $metrics = collect($manager->getMetrics());
        $jobMetrics = $metrics->filter(fn($m) => $m->type === MetricType::JOB_EXECUTION);

        $this->assertCount(1, $jobMetrics);
        $this->assertEquals('processed', $jobMetrics->first()->tags['status']);
        $this->assertEquals('ProcessPodcast', $jobMetrics->first()->tags['job']);
    }

    public function test_it_collects_failed_job_events()
    {
        $manager = app(AuraManager::class);
        
        // Создаем мок задания
        $job = Mockery::mock(\Illuminate\Contracts\Queue\Job::class);
        $job->shouldReceive('resolveName')->andReturn('FailTask');
        $job->shouldReceive('getQueue')->andReturn('high');

        // Используем реальный класс события с моком Job
        // Конструктор JobFailed: ($connectionName, $job, $exception)
        Event::dispatch(new JobFailed('database', $job, new \Exception('Oops')));

        $metrics = collect($manager->getMetrics());
        $jobMetrics = $metrics->filter(fn($m) => $m->type === MetricType::JOB_EXECUTION);

        $this->assertCount(1, $jobMetrics);
        $this->assertEquals('failed', $jobMetrics->first()->tags['status']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
