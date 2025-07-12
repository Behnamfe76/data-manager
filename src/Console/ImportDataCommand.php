<?php

namespace DataManager\Console;

use Illuminate\Console\Command;
use DataManager\Core\DataManager;

class ImportDataCommand extends Command
{
    protected $signature = 'data-manager:import {type} {source} {--chunk=100}';
    protected $description = 'Import data using DataManager';

    public function handle()
    {
        $type = $this->argument('type');
        $source = $this->argument('source');
        $chunkSize = (int)$this->option('chunk');
        $errors = [];
        $manager = app(DataManager::class);
        $count = 0;
        foreach ($manager->import($type, $source, null, null, $errors, $chunkSize) as $chunk) {
            $count += is_array($chunk) ? count($chunk) : 1;
            $this->info("Imported chunk, total so far: $count");
        }
        $this->info("Import complete. Total rows: $count");
        if ($errors) {
            $this->warn("Errors: " . count($errors));
        }
    }
} 