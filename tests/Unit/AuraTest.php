<?php

namespace Aura\Tests\Unit;

use Aura\Aura;
use Aura\Tests\TestCase;

class AuraTest extends TestCase
{
    public function test_it_sets_auth_callback()
    {
        Aura::auth(function($request) {
            return true;
        });

        $this->assertTrue(Aura::check(request()));
    }

    public function test_it_denies_access_when_callback_returns_false()
    {
        Aura::auth(fn() => false);
        $this->assertFalse(Aura::check(request()));
    }

    public function test_it_uses_local_env_check_when_no_callback_set()
    {
        // Reset callback
        $reflection = new \ReflectionClass(Aura::class);
        $property = $reflection->getProperty('authCallback');
        $property->setAccessible(true);
        $property->setValue(null);

        // In testing env, app()->environment('local') is false
        $this->assertFalse(Aura::check(request()));
    }
}
