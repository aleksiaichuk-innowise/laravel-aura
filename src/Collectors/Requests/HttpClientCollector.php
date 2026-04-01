<?php

declare(strict_types=1);

namespace Aura\Collectors\Requests;

use Aura\Contracts\CollectorInterface;
use Aura\Core\AuraManager;
use Aura\DTO\MetricData;
use Aura\DTO\MetricType;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Support\Facades\Event;

class HttpClientCollector implements CollectorInterface
{
    public function __construct(
        protected AuraManager $manager
    ) {
    }

    public function register(): void
    {
        Event::listen(ResponseReceived::class, [$this, 'handleResponseReceived']);
    }

    public function handleResponseReceived(ResponseReceived $event): void
    {
        $this->collect($event);
    }

    protected function collect(ResponseReceived $event): void
    {
        $duration = $event->response->handlerStats()['total_time'] ?? 0;
        $durationMs = $duration * 1000;

        $this->manager->record(new MetricData(
            type: MetricType::EXTERNAL_HTTP_REQUEST,
            value: $durationMs,
            tags: [
                'method' => $event->request->method(),
                'url' => $event->request->url(),
                'status' => $event->response->status(),
                'slow' => $durationMs > config('aura.slow_http_threshold', 1000),
            ]
        ));
    }
}
