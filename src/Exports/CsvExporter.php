<?php

namespace DataManager\Exports;

use DataManager\Contracts\ExporterInterface;

class CsvExporter implements ExporterInterface
{
    /**
     * Export data to a CSV file.
     *
     * @param iterable $data
     * @param string $target Path to the CSV file
     * @return void
     */
    public function export(iterable $data, $target): void
    {
        $handle = fopen($target, 'w');
        if ($handle === false) {
            throw new \RuntimeException("Failed to open CSV file for writing: $target");
        }

        $headerWritten = false;
        foreach ($data as $row) {
            if (!$headerWritten) {
                fputcsv($handle, array_keys($row));
                $headerWritten = true;
            }
            fputcsv($handle, $row);
        }
        fclose($handle);
    }
} 