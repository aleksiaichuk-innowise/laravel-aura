<?php

namespace Aura\Tests;

use Aura\AuraServiceProvider;
use Aura\Core\AuraManager;
use Aura\DTO\MetricData;
use Aura\DTO\MetricType;
use Orchestra\Testbench\TestCase;

class NPlusOneTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [AuraServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
        ]);
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../src/Database/Migrations');
    }

    public function test_it_detects_n_plus_one_problem()
    {
        $manager = app(AuraManager::class);
        $sql = 'select * from "comments" where "post_id" = ?';

        // Имитируем 5 одинаковых запросов (порог nPlusOneThreshold в DatabaseInsight равен 5)
        for ($i = 1; $i <= 5; $i++) {
            $manager->record(new MetricData(
                type: MetricType::DATABASE_QUERY,
                value: 2.5, // 2.5ms (не slow)
                tags: [
                    'sql' => $sql,
                    'bindings' => [$i],
                ],
            ));
        }

        $manager->flush();

        // Проверяем, что в базе появился инсайт о N+1
        // Инсайт создается как новая метрика с тегом 'insight'
        $this->assertDatabaseHas('aura_metrics', [
            'type' => 'database_query',
        ]);

        $recorded = $manager->getStorage()->retrieve(MetricType::DATABASE_QUERY);
        
        $nPlusOneInsights = $recorded->filter(function ($m) {
            return ($m->tags['insight'] ?? '') === 'Possible N+1 problem detected';
        });

        $this->assertCount(1, $nPlusOneInsights, 'Expected exactly one N+1 insight');
        $this->assertEquals('warning', $nPlusOneInsights->first()->tags['severity']);
        
        echo "Success: N+1 problem detected correctly and only once.\n";
    }
}
