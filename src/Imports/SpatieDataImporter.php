<?php

namespace DataManager\Imports;

use DataManager\Contracts\ImporterInterface;

class SpatieDataImporter implements ImporterInterface
{
    /**
     * Import data from Spatie Data objects.
     *
     * @param array $source Array of Spatie Data objects
     * @return iterable
     */
    public function import($source): iterable
    {
        foreach ($source as $dataObject) {
            if (method_exists($dataObject, 'toArray')) {
                yield $dataObject->toArray();
            } else {
                yield (array) $dataObject;
            }
        }
    }
} 