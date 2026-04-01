<?php

namespace Aura\Tests\Unit;

use Aura\Core\DataMasker;
use Aura\Tests\TestCase;

class DataMaskerTest extends TestCase
{
    public function test_it_masks_sensitive_keys()
    {
        $masker = new DataMasker();
        $data = [
            'id' => 1,
            'username' => 'admin',
            'password' => 'secret_123',
            'api_token' => 'abc-123',
            'nested' => [
                'db_password' => 'qwerty'
            ]
        ];

        $masked = $masker->mask($data);

        $this->assertEquals(1, $masked['id']);
        $this->assertEquals('admin', $masked['username']);
        $this->assertEquals('********', $masked['password']);
        $this->assertEquals('********', $masked['api_token']);
        $this->assertEquals('********', $masked['nested']['db_password']);
    }
}
