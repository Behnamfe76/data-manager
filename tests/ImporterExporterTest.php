<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use DataManager\Imports\CsvImporter;
use DataManager\Exports\CsvExporter;

class ImporterExporterTest extends TestCase
{
    public function testCsvImportExport(): void
    {
        $data = [
            ['name' => 'Alice', 'email' => 'alice@example.com'],
            ['name' => 'Bob', 'email' => 'bob@example.com'],
        ];
        $csvFile = __DIR__ . '/test.csv';

        // Export
        $exporter = new CsvExporter();
        $exporter->export($data, $csvFile);
        $this->assertFileExists($csvFile);

        // Import
        $importer = new CsvImporter();
        $imported = iterator_to_array($importer->import($csvFile));
        $this->assertEquals($data, $imported);

        // Cleanup
        unlink($csvFile);
    }
} 