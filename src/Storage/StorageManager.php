<?php

declare(strict_types=1);

namespace Aura\Storage;

use Aura\Contracts\StorageInterface;
use Illuminate\Support\Manager;

class StorageManager extends Manager implements StorageInterface
{
    public function getDefaultDriver(): string
    {
        return $this->config->get('aura.driver', 'database');
    }

    protected function createDatabaseDriver(): DatabaseStorage
    {
        return $this->container->make(DatabaseStorage::class);
    }

    protected function createRedisDriver(): RedisStorage
    {
        return $this->container->make(RedisStorage::class);
    }

    protected function createNullDriver(): NullStorage
    {
        return new NullStorage();
    }

    /**
     * @param \Aura\DTO\Metrics\MetricData $metric
     */
    public function store($metric): void
    {
        $this->driver()->store($metric);
    }

    /**
     * @param array $metrics
     */
    public function storeBatch(array $metrics): void
    {
        $this->driver()->storeBatch($metrics);
    }

    /**
     * @param \Aura\DTO\Metrics\MetricType $type
     * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    public function retrieve($type, array $filters = []): \Illuminate\Support\Collection
    {
        return $this->driver()->retrieve($type, $filters);
    }

    /**
     * @param \DateTimeInterface $before
     */
    public function prune(\DateTimeInterface $before): void
    {
        $this->driver()->prune($before);
    }
}
