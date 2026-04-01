<?php

namespace Aura\Tests;

use Aura\AuraServiceProvider;
use Aura\Core\AuraManager;
use Aura\DTO\MetricData;
use Aura\DTO\MetricType;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\TestCase;

class VerificationTest extends TestCase
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
            'prefix'   => '',
        ]);
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../src/Database/Migrations');
    }

    public function test_it_records_metrics()
    {
        $manager = app(AuraManager::class);
        
        $manager->record(new MetricData(
            type: MetricType::DATABASE_QUERY,
            value: 100,
            tags: [
                'sql' => 'select * from users',
                'slow' => true,
            ],
        ));

        $manager->flush();

        $this->assertDatabaseHas('aura_metrics', [
            'type' => 'database_query',
            'value' => 100,
        ]);
        
        echo "Success: Metric recorded and verified in DB.\n";
    }
}
