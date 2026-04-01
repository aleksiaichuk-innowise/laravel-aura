<?php

namespace Aura\Tests\Unit;

use Aura\AuraServiceProvider;
use Aura\Core\Tracker;
use Aura\Tests\TestCase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Mockery;

class LogIntegrationTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        
        // Ensure log integration is enabled for this test
        Config::set('aura.log_integration.enabled', true);
        Config::set('aura.log_integration.context_key', 'test_trace_id');
    }
public function test_it_integrates_with_logs_when_enabled()
{
    $tracker = app(Tracker::class);
    $traceId = $tracker->getTraceId();
    $key = Config::get('aura.log_integration.context_key');

    // We create a mock and make sure is_callable([$mock, 'withContext']) is true
    $logMock = Mockery::mock('Illuminate\Log\LogManager');
    $logMock->shouldReceive('withContext')
        ->once()
        ->with([$key => $traceId]);

    // This helps is_callable to return true for magic methods if they are explicitly mocked
    $logMock->shouldReceive('withContext')->andReturnSelf(); 

    $this->app->instance('log', $logMock);

    $provider = new AuraServiceProvider($this->app);
    $reflection = new \ReflectionClass(AuraServiceProvider::class);
    $method = $reflection->getMethod('integrateWithLogs');
    $method->setAccessible(true);

    $method->invoke($provider);
}


    public function test_it_does_not_integrate_when_disabled()
    {
        Config::set('aura.log_integration.enabled', false);

        $logMock = Mockery::mock('Illuminate\Log\LogManager[withContext]', [app()]);
        $logMock->shouldReceive('withContext')->never();
        
        $this->app->instance('log', $logMock);
        
        $provider = new AuraServiceProvider($this->app);
        $reflection = new \ReflectionClass(AuraServiceProvider::class);
        $method = $reflection->getMethod('integrateWithLogs');
        $method->setAccessible(true);
        
        $method->invoke($provider);
    }

    public function test_it_respects_custom_context_key()
    {
        $customKey = 'custom_aura_id';
        Config::set('aura.log_integration.context_key', $customKey);
        
        $tracker = app(Tracker::class);
        $traceId = $tracker->getTraceId();
        
        $logMock = Mockery::mock('Illuminate\Log\LogManager[withContext]', [app()]);
        $logMock->shouldReceive('withContext')
            ->once()
            ->with([$customKey => $traceId]);

        $this->app->instance('log', $logMock);

        $provider = new AuraServiceProvider($this->app);
        $reflection = new \ReflectionClass(AuraServiceProvider::class);
        $method = $reflection->getMethod('integrateWithLogs');
        $method->setAccessible(true);
        
        $method->invoke($provider);
    }
}
