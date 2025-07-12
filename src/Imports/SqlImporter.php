<?php

namespace DataManager\Imports;

use DataManager\Contracts\ImporterInterface;

class SqlImporter implements ImporterInterface
{
    /**
     * Import data from an SQL file (returns SQL statements as strings).
     *
     * @param string $source Path to the SQL file
     * @return iterable
     */
    public function import($source): iterable
    {
        if (!is_readable($source)) {
            throw new \InvalidArgumentException("SQL file not readable: $source");
        }

        $sqlContent = file_get_contents($source);
        if ($sqlContent === false) {
            throw new \RuntimeException("Failed to read SQL file: $source");
        }

        // Split SQL file into individual statements (rudimentary, not SQL parser)
        $statements = array_filter(array_map('trim', explode(';', $sqlContent)));
        foreach ($statements as $statement) {
            if ($statement !== '') {
                yield $statement . ';';
            }
        }
    }
} 