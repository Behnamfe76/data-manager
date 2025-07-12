<?php

namespace DataManager\Exports;

use DataManager\Contracts\ExporterInterface;
use Illuminate\Database\Eloquent\Model;

class ModelExporter implements ExporterInterface
{
    /**
     * Export data from a database model.
     *
     * @param string|Model $data Model class name or instance
     * @param mixed $target (optional) Not used, for interface compatibility
     * @return void
     */
    public function export(iterable $data, $target): void
    {
        $result = [];
        foreach ($data as $model) {
            if ($model instanceof Model) {
                $result[] = $model->toArray();
            }
        }
        // You can write $result to a file or handle as needed, depending on $target
        // For now, this method just collects the data for export
    }
} 