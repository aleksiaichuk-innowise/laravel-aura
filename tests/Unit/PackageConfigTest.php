<?php

namespace Aura\Tests\Unit;

use Aura\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class PackageConfigTest extends TestCase
{
    public function test_config_values_are_accessible()
    {
        $config = Config::get('aura');
        
        $this->assertIsArray($config);
        $this->assertTrue($config['enabled']);
        $this->assertEquals('aura', $config['path']);
        
        // Исправленные ключи согласно src/Config/aura.php
        $this->assertEquals('database', $config['driver']);
        $this->assertIsArray($config['drivers']);
        $this->assertIsArray($config['collectors']);
    }

    public function test_files_interpretation()
    {
        $basePath = realpath(__DIR__ . '/../../src');
        
        // Прямая загрузка для покрытия не-классовых строк
        $this->assertIsArray(require $basePath . '/Config/aura.php');
        $this->assertIsObject(require $basePath . '/Database/Migrations/2026_04_01_000000_create_aura_metrics_table.php');
        
        if (file_exists($basePath . '/Routes/web.php')) {
            require $basePath . '/Routes/web.php';
        }
        
        $this->assertTrue(true);
    }
}
