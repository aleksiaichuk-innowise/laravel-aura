<?php

namespace Aura\Tests\Feature\Collectors;

use Aura\Collectors\RequestDurationCollector;
use Aura\Core\AuraManager;
use Aura\DTO\MetricType;
use Aura\Tests\TestCase;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;

class RequestDurationCollectorTest extends TestCase
{
    public function test_it_records_request_duration()
    {
        $manager = app(AuraManager::class);
        $request = Request::create('/test', 'GET');
        $response = new Response();

        // Эмулируем событие завершения обработки запроса
        Event::dispatch(new RequestHandled($request, $response));

        $manager->flush();

        $this->assertDatabaseHas('aura_metrics', [
            'type' => MetricType::REQUEST_DURATION->value,
        ]);

        $metric = \Illuminate\Support\Facades\DB::table('aura_metrics')->first();
        $tags = json_decode($metric->tags, true);
        
        $this->assertEquals('GET', $tags['method']);
        $this->assertEquals('/test', $tags['path']);
        $this->assertEquals(200, $tags['status']);
    }
}
