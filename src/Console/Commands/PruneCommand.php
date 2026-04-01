<?php

declare(strict_types=1);

namespace Aura\Console\Commands;

use Aura\Contracts\StorageInterface;
use Illuminate\Console\Command;

class PruneCommand extends Command
{
    protected $signature = 'aura:prune {--hours=24 : The number of hours to retain data}';

    protected $description = 'Prune old Aura metrics from storage';

    /**
     * @param StorageInterface $storage
     * @return void
     */
    public function handle(StorageInterface $storage): void
    {
        $hours = (int) $this->option('hours');
        $before = now()->subHours($hours);

        $this->info("Pruning metrics recorded before {$before->toDateTimeString()}...");

        $storage->prune($before);

        $this->info('Metrics pruned successfully.');
    }
}
