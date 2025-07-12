<?php

namespace DataManager\Imports;

use DataManager\Contracts\ImporterInterface;

class JsonImporter implements ImporterInterface
{
    /**
     * Import data from a JSON file.
     *
     * @param string $source Path to the JSON file
     * @return iterable
     */
    public function import($source): iterable
    {
        if (!is_readable($source)) {
            throw new \InvalidArgumentException("JSON file not readable: $source");
        }

        $json = file_get_contents($source);
        if ($json === false) {
            throw new \RuntimeException("Failed to read JSON file: $source");
        }

        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Invalid JSON: " . json_last_error_msg());
        }

        foreach ($data as $item) {
            yield $item;
        }
    }
} 