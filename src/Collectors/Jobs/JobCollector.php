<?php

declare(strict_types=1);

namespace Aura\Collectors\Jobs;

use Aura\Contracts\CollectorInterface;
use Aura\Core\AuraManager;
use Aura\DTO\MetricData;
use Aura\DTO\MetricType;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Event;

class JobCollector implements CollectorInterface
{
    public function __construct(
        protected AuraManager $manager
    ) {
    }

    public function register(): void
    {
        Event::listen(JobProcessed::class, fn($event) => $this->collect($event, 'processed'));
        Event::listen(JobFailed::class, fn($event) => $this->collect($event, 'failed'));
    }

    protected function collect($event, string $status): void
    {
        // Many queue drivers don't provide duration directly in the event, 
        // we might need to store start time in JobProcessing.
        // For this showcase, we use basic info.
        
        $this->manager->record(new MetricData(
            type: MetricType::JOB_EXECUTION,
            value: 1, // Represents one execution
            tags: [
                'job' => $event->job->resolveName(),
                'connection' => $event->connectionName,
                'queue' => $event->job->getQueue(),
                'status' => $status,
            ]
        ));
    }
}
