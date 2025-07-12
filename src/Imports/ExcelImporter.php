<?php

namespace DataManager\Imports;

use DataManager\Contracts\ImporterInterface;
use Maatwebsite\Excel\Facades\Excel;

class ExcelImporter implements ImporterInterface
{
    /**
     * Import data from an Excel file.
     *
     * @param string $source Path to the Excel file
     * @return iterable
     */
    public function import($source): iterable
    {
        if (!is_readable($source)) {
            throw new \InvalidArgumentException("Excel file not readable: $source");
        }

        $rows = Excel::toArray(null, $source);
        // $rows is an array of sheets, each sheet is an array of rows
        foreach ($rows as $sheet) {
            $header = null;
            foreach ($sheet as $row) {
                if ($header === null) {
                    $header = $row;
                    continue;
                }
                yield array_combine($header, $row);
            }
        }
    }
} 