<?php

namespace Kartinov\GdprToolkit\Console;

use Illuminate\Console\Command;
use Kartinov\GdprToolkit\GdprToolkitManager;

class ScanCommand extends Command
{
    protected $signature = 'gdpr:scan';

    protected $description = 'Scan models for personal data fields';

    public function handle(GdprToolkitManager $manager)
    {
        $this->info($manager->scan());
    }
}
