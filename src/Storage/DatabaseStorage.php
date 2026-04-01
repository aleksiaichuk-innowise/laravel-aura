<?php

declare(strict_types=1);

namespace Aura\Storage;

use Aura\Contracts\StorageInterface;
use Aura\DTO\MetricData;
use Aura\DTO\MetricType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseStorage implements StorageInterface
{
    protected string $table;

    public function __construct()
    {
        $this->table = config('aura.database.table', 'aura_metrics');
    }

    public function store(MetricData $metric): void
    {
        DB::table($this->table)->insert($this->serialize($metric));
    }

    public function storeBatch(array $metrics): void
    {
        $data = array_map(fn(MetricData $m) => $this->serialize($m), $metrics);
        
        DB::table($this->table)->insert($data);
    }

    public function retrieve(MetricType $type, array $filters = []): Collection
    {
        return DB::table($this->table)
            ->where('type', $type->value)
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();
    }

    public function prune(\DateTimeInterface $before): void
    {
        DB::table($this->table)
            ->where('created_at', '<', $before)
            ->delete();
    }

    protected function serialize(MetricData $metric): array
    {
        return [
            'id' => (string) Str::uuid(),
            'type' => $metric->type->value,
            'value' => $metric->value,
            'tags' => json_encode($metric->tags),
            'created_at' => date('Y-m-d H:i:s', (int) $metric->getTimestamp()),
        ];
    }
}
