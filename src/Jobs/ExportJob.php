<?php

namespace DataManager\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use DataManager\Core\DataManager;

class ExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $type;
    protected $data;
    protected $target;
    protected $transformer;
    protected $chunkSize;

    public function __construct($type, $data, $target, $transformer = null, $chunkSize = null)
    {
        $this->type = $type;
        $this->data = $data;
        $this->target = $target;
        $this->transformer = $transformer;
        $this->chunkSize = $chunkSize;
    }

    public function handle()
    {
        $manager = app(DataManager::class);
        $manager->export($this->type, $this->data, $this->target, $this->transformer, $this->chunkSize);
    }
} 