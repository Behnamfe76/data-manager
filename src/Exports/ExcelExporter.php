<?php

namespace DataManager\Exports;

use DataManager\Contracts\ExporterInterface;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExcelExporter implements ExporterInterface
{
    /**
     * Export data to an Excel file.
     *
     * @param iterable $data
     * @param string $target Path to the Excel file
     * @return void
     */
    public function export(iterable $data, $target): void
    {
        $arrayData = is_array($data) ? $data : iterator_to_array($data);
        if (empty($arrayData)) {
            throw new \InvalidArgumentException('No data to export.');
        }
        $headings = array_keys(reset($arrayData));
        $export = new class($arrayData, $headings) implements FromArray, WithHeadings {
            private $data;
            private $headings;
            public function __construct($data, $headings) {
                $this->data = $data;
                $this->headings = $headings;
            }
            public function array(): array {
                return $this->data;
            }
            public function headings(): array {
                return $this->headings;
            }
        };
        Excel::store($export, $target);
    }
} 