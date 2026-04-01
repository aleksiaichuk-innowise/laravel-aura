<?php

namespace Aura\Tests\Feature\Collectors;

use Aura\Core\AuraManager;
use Aura\DTO\MetricType;
use Aura\Tests\TestCase;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Event;
use Mockery;

class HttpClientCollectorTest extends TestCase
{
    public function test_it_collects_http_client_response_events()
    {
        $manager = app(AuraManager::class);
        
        // Создаем моки запроса и ответа
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('method')->andReturn('POST');
        $request->shouldReceive('url')->andReturn('https://api.github.com/graphql');

        $response = Mockery::mock(Response::class);
        $response->shouldReceive('status')->andReturn(200);
        $response->shouldReceive('handlerStats')->andReturn(['total_time' => 0.5]); // 500ms

        // Эмулируем событие завершения HTTP-запроса
        Event::dispatch(new ResponseReceived($request, $response));
        
        $metrics = collect($manager->getMetrics());
        $httpMetrics = $metrics->filter(fn($m) => $m->type === MetricType::EXTERNAL_HTTP_REQUEST);

        $this->assertCount(1, $httpMetrics);
        $this->assertEquals(500, $httpMetrics->first()->value); // 0.5 * 1000
        $this->assertEquals('POST', $httpMetrics->first()->tags['method']);
        $this->assertEquals('https://api.github.com/graphql', $httpMetrics->first()->tags['url']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
