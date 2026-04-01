<?php

namespace Aura\Tests\Feature;

use Aura\Aura;
use Aura\Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;

class DashboardSecurityTest extends TestCase
{
    public function test_it_blocks_access_to_dashboard_by_default(): void
    {
        Aura::auth(fn() => false);
        $response = $this->get('/aura');
        
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_it_allows_access_when_authorized(): void
    {
        Aura::auth(static function ($request) {
            return true;
        });

        $response = $this->get('/aura');

        $response->assertStatus(200);
    }
}
