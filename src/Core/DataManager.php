<?php

namespace DataManager\Core;

use DataManager\Imports\CsvImporter;
use DataManager\Exports\CsvExporter;
use DataManager\Imports\JsonImporter;
use DataManager\Exports\JsonExporter;
use DataManager\Imports\XmlImporter;
use DataManager\Exports\XmlExporter;
use DataManager\Imports\SqlImporter;
use DataManager\Exports\SqlExporter;
use DataManager\Imports\ExcelImporter;
use DataManager\Exports\ExcelExporter;
use DataManager\Imports\ModelImporter;
use DataManager\Exports\ModelExporter;
use DataManager\Imports\SpatieDataImporter;
use DataManager\Exports\SpatieDataExporter;

class DataManager
{
    public function import(string $type, $source)
    {
        switch (strtolower($type)) {
            case 'csv':
                return (new CsvImporter())->import($source);
            case 'json':
                return (new JsonImporter())->import($source);
            case 'xml':
                return (new XmlImporter())->import($source);
            case 'sql':
                return (new SqlImporter())->import($source);
            case 'excel':
                return (new ExcelImporter())->import($source);
            case 'model':
                return (new ModelImporter())->import($source);
            case 'spatie':
                return (new SpatieDataImporter())->import($source);
            default:
                throw new \InvalidArgumentException("Unsupported import type: $type");
        }
    }

    public function export(string $type, iterable $data, $target)
    {
        switch (strtolower($type)) {
            case 'csv':
                return (new CsvExporter())->export($data, $target);
            case 'json':
                return (new JsonExporter())->export($data, $target);
            case 'xml':
                return (new XmlExporter())->export($data, $target);
            case 'sql':
                return (new SqlExporter())->export($data, $target);
            case 'excel':
                return (new ExcelExporter())->export($data, $target);
            case 'model':
                return (new ModelExporter())->export($data, $target);
            case 'spatie':
                return (new SpatieDataExporter())->export($data, $target);
            default:
                throw new \InvalidArgumentException("Unsupported export type: $type");
        }
    }
} 