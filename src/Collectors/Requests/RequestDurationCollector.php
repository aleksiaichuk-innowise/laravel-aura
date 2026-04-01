<?php

declare(strict_types=1);

namespace Aura\Collectors\Requests;

use Aura\Contracts\CollectorInterface;
use Aura\Core\AuraManager;
use Aura\DTO\MetricData;
use Aura\DTO\MetricType;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\Event;

class RequestDurationCollector implements CollectorInterface
{
    public function __construct(
        protected AuraManager $manager
    ) {
    }

    public function register(): void
    {
        Event::listen(RequestHandled::class, function (RequestHandled $event) {
            $this->collect($event);
        });
    }

    protected function collect(RequestHandled $event): void
    {
        $startTime = defined('LARAVEL_START') ? LARAVEL_START : microtime(true);
        $duration = (microtime(true) - $startTime) * 1000;

        $this->manager->record(new MetricData(
            type: MetricType::REQUEST_DURATION,
            value: $duration,
            tags: [
                'method' => $event->request->getMethod(),
                'path' => $event->request->getPathInfo(),
                'status' => $event->response->getStatusCode(),
            ]
        ));
    }
}
