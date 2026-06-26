<?php

namespace Kartinov\GdprToolkit\Console;

use Illuminate\Console\Command;
use Kartinov\GdprToolkit\GdprToolkitManager;

class ScanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gdpr:scan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan subject models and generate a draft RoPA JSON file';

    /**
     * Execute the console command.
     */
    public function handle(GdprToolkitManager $manager)
    {
        $this->info($manager->scan());
    }
}
