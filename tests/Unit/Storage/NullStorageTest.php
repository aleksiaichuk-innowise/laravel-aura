<?php

namespace Aura\Tests\Unit\Storage;

use Aura\DTO\MetricData;
use Aura\DTO\MetricType;
use Aura\Storage\NullStorage;
use Aura\Tests\TestCase;

class NullStorageTest extends TestCase
{
    public function test_it_does_nothing_on_store()
    {
        $storage = new NullStorage();
        $metric = new MetricData(MetricType::MEMORY_USAGE, 100);
        
        $storage->store($metric);
        $storage->storeBatch([$metric]);
        $storage->prune(new \DateTime());
        
        $this->assertEmpty($storage->retrieve(MetricType::MEMORY_USAGE));
    }
}
