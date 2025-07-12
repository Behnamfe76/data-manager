<?php

namespace DataManager\Exports;

use DataManager\Contracts\ExporterInterface;

class SpatieDataExporter implements ExporterInterface
{
    /**
     * Export data to Spatie Data objects.
     *
     * @param iterable $data
     * @param string $target Spatie Data class name
     * @return void
     */
    public function export(iterable $data, $target): void
    {
        if (!class_exists($target)) {
            throw new \InvalidArgumentException("Target Spatie Data class does not exist: $target");
        }
        $result = [];
        foreach ($data as $item) {
            $result[] = new $target($item);
        }
        // You can use $result as needed, e.g., return or save
    }
} 