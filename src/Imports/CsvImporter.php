<?php

namespace DataManager\Imports;

use DataManager\Contracts\ImporterInterface;

class CsvImporter implements ImporterInterface
{
    /**
     * Import data from a CSV file.
     *
     * @param string $source Path to the CSV file
     * @return iterable
     */
    public function import($source): iterable
    {
        if (!is_readable($source)) {
            throw new \InvalidArgumentException("CSV file not readable: $source");
        }

        $handle = fopen($source, 'r');
        if ($handle === false) {
            throw new \RuntimeException("Failed to open CSV file: $source");
        }

        $header = null;
        while (($row = fgetcsv($handle)) !== false) {
            if ($header === null) {
                $header = $row;
                continue;
            }
            yield array_combine($header, $row);
        }
        fclose($handle);
    }
} 