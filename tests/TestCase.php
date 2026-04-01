<?php

namespace Aura\Tests;

use Aura\AuraServiceProvider;
use Aura\Core\DataMasker;
use Aura\Core\InsightEngine;
use Aura\Core\Tracker;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            AuraServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../src/Database/Migrations');
    }

    protected function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('aura.enabled', true);
        
        // Настройка Redis для Docker
        config()->set('database.redis.client', 'phpredis');
        config()->set('database.redis.default.host', 'redis');
        config()->set('aura.redis.host', 'redis');

        // Принудительно устанавливаем Middleware, чтобы не зависеть от кэша опубликованных конфигов
        config()->set('aura.middleware', [
            'web',
            \Aura\Http\Middleware\AuraShieldMiddleware::class,
        ]);

        // Убеждаемся, что все зависимости ядра доступны в контейнере
        $app->singleton(Tracker::class, fn() => new Tracker());
        $app->singleton(DataMasker::class, fn() => new DataMasker());
        $app->singleton(InsightEngine::class, fn() => new InsightEngine());
    }
}
