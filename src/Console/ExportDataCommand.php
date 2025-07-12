<?php

namespace DataManager\Console;

use Illuminate\Console\Command;
use DataManager\Core\DataManager;

class ExportDataCommand extends Command
{
    protected $signature = 'data-manager:export {type} {data} {target} {--chunk=100}';
    protected $description = 'Export data using DataManager';

    public function handle()
    {
        $type = $this->argument('type');
        $dataArg = $this->argument('data');
        $target = $this->argument('target');
        $chunkSize = (int)$this->option('chunk');
        $manager = app(DataManager::class);
        $data = [];
        // For demo: if dataArg is a file, load as JSON; else try to resolve as class
        if (is_file($dataArg)) {
            $data = json_decode(file_get_contents($dataArg), true);
        } elseif (class_exists($dataArg)) {
            $data = $dataArg::all();
        }
        $manager->export($type, $data, $target, null, $chunkSize);
        $this->info("Export complete to $target");
    }
} 