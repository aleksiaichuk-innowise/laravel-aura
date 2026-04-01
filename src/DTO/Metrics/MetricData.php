<?php

declare(strict_types=1);

namespace Aura\DTO\Metrics;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements Arrayable<string, mixed>
 */
readonly class MetricData implements Arrayable
{
    /**
     * @param array<string, mixed> $tags
     */
    public function __construct(
        public MetricType $type,
        public float|int $value,
        public array $tags = [],
        public ?string $traceId = null,
        public ?float $timestamp = null,
    ) {
    }

    public function getTimestamp(): float
    {
        return $this->timestamp ?? microtime(true);
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'value' => $this->value,
            'tags' => $this->tags,
            'trace_id' => $this->traceId,
            'timestamp' => $this->getTimestamp(),
        ];
    }
}
