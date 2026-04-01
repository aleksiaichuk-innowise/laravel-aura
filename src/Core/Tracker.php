<?php

declare(strict_types=1);

namespace Aura\Core;

use Illuminate\Support\Str;

class Tracker
{
    protected ?string $traceId = null;

    public function getTraceId(): string
    {
        if ($this->traceId === null) {
            $this->traceId = (string) Str::uuid();
        }

        return $this->traceId;
    }

    public function setTraceId(string $traceId): void
    {
        $this->traceId = $traceId;
    }
}
