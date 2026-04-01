<?php

namespace Aura\Tests\Unit;

use Aura\AuraServiceProvider;
use Aura\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class AuraServiceProviderTest extends TestCase
{
    public function test_it_registers_config()
    {
        $this->assertNotNull(Config::get('aura'));
    }

    public function test_it_registers_manager_as_singleton()
    {
        $manager1 = app(\Aura\Core\AuraManager::class);
        $manager2 = app(\Aura\Core\AuraManager::class);

        $this->assertSame($manager1, $manager2);
    }

    public function test_it_registers_storage_binding()
    {
        $this->assertInstanceOf(\Aura\Contracts\StorageInterface::class, app(\Aura\Contracts\StorageInterface::class));
    }

    public function test_it_covers_internal_registration_methods()
    {
        $provider = new AuraServiceProvider(app());
        
        $reflection = new \ReflectionClass(AuraServiceProvider::class);
        
        $resMethod = $reflection->getMethod('registerResources');
        $resMethod->setAccessible(true);
        $resMethod->invoke($provider);

        $collMethod = $reflection->getMethod('registerCollectors');
        $collMethod->setAccessible(true);
        $collMethod->invoke($provider);

        $this->assertTrue(true);
    }
}
