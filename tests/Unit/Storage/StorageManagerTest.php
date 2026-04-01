<?php

namespace Aura\Tests\Unit\Storage;

use Aura\Storage\StorageManager;
use Aura\Tests\TestCase;

class StorageManagerTest extends TestCase
{
    public function test_it_can_create_all_drivers()
    {
        $manager = app(StorageManager::class);
        
        $this->assertInstanceOf(\Aura\Storage\DatabaseStorage::class, $manager->driver('database'));
        $this->assertInstanceOf(\Aura\Storage\RedisStorage::class, $manager->driver('redis'));
        $this->assertInstanceOf(\Aura\Storage\NullStorage::class, $manager->driver('null'));
    }

    public function test_it_gets_default_driver()
    {
        $manager = app(StorageManager::class);
        $this->assertEquals('database', $manager->getDefaultDriver());
    }
}
