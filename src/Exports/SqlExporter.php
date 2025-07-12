<?php

namespace DataManager\Exports;

use DataManager\Contracts\ExporterInterface;

class SqlExporter implements ExporterInterface
{
    /**
     * Export SQL statements to a file.
     *
     * @param iterable $data SQL statements as strings
     * @param string $target Path to the SQL file
     * @return void
     */
    public function export(iterable $data, $target): void
    {
        $handle = fopen($target, 'w');
        if ($handle === false) {
            throw new \RuntimeException("Failed to open SQL file for writing: $target");
        }
        foreach ($data as $statement) {
            fwrite($handle, rtrim($statement) . ";\n");
        }
        fclose($handle);
    }
} 