<?php

namespace DataManager\Exports;

use DataManager\Contracts\ExporterInterface;

class JsonExporter implements ExporterInterface
{
    /**
     * Export data to a JSON file.
     *
     * @param iterable $data
     * @param string $target Path to the JSON file
     * @return void
     */
    public function export(iterable $data, $target): void
    {
        $arrayData = is_array($data) ? $data : iterator_to_array($data);
        $json = json_encode($arrayData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new \RuntimeException("Failed to encode data as JSON: " . json_last_error_msg());
        }
        if (file_put_contents($target, $json) === false) {
            throw new \RuntimeException("Failed to write JSON file: $target");
        }
    }
} 