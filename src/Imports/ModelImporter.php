<?php

namespace DataManager\Imports;

use DataManager\Contracts\ImporterInterface;
use Illuminate\Database\Eloquent\Model;

class ModelImporter implements ImporterInterface
{
    /**
     * Import data into a database model.
     *
     * @param array $source [model_class, data]
     * @return iterable
     */
    public function import($source): iterable
    {
        if (!is_array($source) || count($source) !== 2) {
            throw new \InvalidArgumentException('Source must be an array: [model_class, data]');
        }
        [$modelClass, $data] = $source;
        if (!is_subclass_of($modelClass, Model::class)) {
            throw new \InvalidArgumentException('First element must be a valid Eloquent model class.');
        }
        foreach ($data as $attributes) {
            $model = new $modelClass($attributes);
            $model->save();
            yield $model;
        }
    }
} 