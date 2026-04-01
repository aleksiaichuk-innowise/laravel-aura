<?php

declare(strict_types=1);

namespace Aura;

use Aura\Console\Commands\InstallCommand;
use Aura\Console\Commands\PruneCommand;
use Aura\Contracts\StorageInterface;
use Aura\Core\AuraManager;
use Aura\Core\DataMasker;
use Aura\Core\InsightEngine;
use Aura\Core\Tracker;
use Aura\Http\Middleware\AuraShieldMiddleware;
use Aura\Insights\DatabaseInsight;
use Aura\Storage\StorageManager;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;

class AuraServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/Config/aura.php', 'aura');

        $this->app->singleton(Tracker::class);
        $this->app->singleton(DataMasker::class);

        $this->app->singleton(StorageManager::class);
        $this->app->singleton(StorageInterface::class, function ($app) {
            return $app->make(StorageManager::class);
        });

        $this->app->singleton(InsightEngine::class, function ($app) {
            return new InsightEngine([
                $app->make(DatabaseInsight::class),
            ]);
        });

        $this->app->singleton(AuraManager::class);
    }

    /**
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        if (!config('aura.enabled')) {
            return;
        }

        $this->app['router']->aliasMiddleware('aura.auth', AuraShieldMiddleware::class);

        $this->registerResources();
        $this->integrateWithLogs();

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                PruneCommand::class,
            ]);
        }

        $this->app->terminating(function () {
            $this->app->make(AuraManager::class)->flush();
        });

        $this->registerCollectors();
    }

    /**
     * Integrate with Laravel's logging system.
     */
    protected function integrateWithLogs(): void
    {
        if (!config('aura.log_integration.enabled', true)) {
            return;
        }

        $tracker = $this->app->make(Tracker::class);
        $key = config('aura.log_integration.context_key', 'aura_trace_id');

        if (is_callable([$this->app['log'], 'withContext'])) {
            $this->app['log']->withContext([
                $key => $tracker->getTraceId(),
            ]);
        }
    }

    protected function registerResources(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/Config/aura.php' => config_path('aura.php'),
            ], 'aura-config');

            $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        }

        $this->loadViewsFrom(__DIR__.'/Resources/views', 'aura');
        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');
    }

    /**
     * @throws BindingResolutionException
     */
    protected function registerCollectors(): void
    {
        $collectors = config('aura.collectors', []);

        foreach ($collectors as $collector) {
            $this->app->make($collector)->register();
        }
    }
}
