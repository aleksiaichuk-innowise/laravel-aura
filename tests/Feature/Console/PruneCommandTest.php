<?php

namespace Aura\Tests\Feature\Console;

use Aura\Contracts\StorageInterface;
use Aura\DTO\Metrics\MetricData;
use Aura\DTO\Metrics\MetricType;
use Aura\Tests\TestCase;

class PruneCommandTest extends TestCase
{
    public function test_it_prunes_old_metrics()
    {
        $storage = app(StorageInterface::class);
        $storage->store(new MetricData(MetricType::MEMORY_USAGE, 100));

        // Эмулируем запуск команды очистки
        $this->artisan('aura:prune', ['--hours' => 1])
            ->expectsOutputToContain('Pruning metrics recorded before')
            ->expectsOutput('Metrics pruned successfully.')
            ->assertExitCode(0);
    }
}
