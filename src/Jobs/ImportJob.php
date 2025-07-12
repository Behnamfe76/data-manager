<?php

namespace DataManager\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use DataManager\Core\DataManager;

class ImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $type;
    protected $source;
    protected $transformer;
    protected $validator;
    protected $chunkSize;

    public function __construct($type, $source, $transformer = null, $validator = null, $chunkSize = null)
    {
        $this->type = $type;
        $this->source = $source;
        $this->transformer = $transformer;
        $this->validator = $validator;
        $this->chunkSize = $chunkSize;
    }

    public function handle()
    {
        $errors = [];
        $manager = app(DataManager::class);
        foreach ($manager->import($this->type, $this->source, $this->transformer, $this->validator, $errors, $this->chunkSize) as $chunkOrRow) {
            // Process chunk or row (customize as needed)
        }
        // Optionally handle $errors
    }
} 