<?php

declare(strict_types=1);

namespace Aura\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'aura:install';

    protected $description = 'Install the Aura Monitoring package';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Installing Aura...');

        $this->publishConfiguration();
        $this->runMigrations();

        $this->info('Aura installed successfully.');
        $this->info('You can access the dashboard at: ' . config('aura.path', 'aura'));
    }

    /**
     * @return void
     */
    protected function publishConfiguration(): void
    {
        $this->call('vendor:publish', [
            '--tag' => 'aura-config',
        ]);
    }

    /**
     * @return void
     */
    protected function runMigrations(): void
    {
        if ($this->confirm('Do you want to run the migrations now?')) {
            $this->call('migrate');
        }
    }
}
