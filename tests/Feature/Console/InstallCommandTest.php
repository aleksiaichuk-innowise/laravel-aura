<?php

namespace Aura\Tests\Feature\Console;

use Aura\Tests\TestCase;
use Illuminate\Support\Facades\File;

class InstallCommandTest extends TestCase
{
    public function test_it_installs_the_package()
    {
        // Эмулируем запуск команды установки
        $this->artisan('aura:install')
            ->expectsOutput('Installing Aura...')
            ->expectsConfirmation('Do you want to run the migrations now?', 'no')
            ->expectsOutput('Aura installed successfully.')
            ->assertExitCode(0);
    }
}
