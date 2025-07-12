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
    protected $transformer = null;
    protected $validator = null;

    /**
     * Register a custom transformer (callable or DataTransformerInterface).
     *
     * @param callable|object|null $transformer
     * @return $this
     */
    public function setTransformer($transformer)
    {
        $this->transformer = $transformer;
        return $this;
    }

    /**
     * Register a custom validator (callable or ValidatorInterface).
     *
     * @param callable|object|null $validator
     * @return $this
     */
    public function setValidator($validator)
    {
        $this->validator = $validator;
        return $this;
    }

    /**
     * Import data with optional transformer and validator.
     *
     * @param string $type
     * @param mixed $source
     * @param callable|object|null $transformer
     * @param callable|object|null $validator
     * @param array $errors (by reference) - collects validation errors
     * @return iterable
     */
    public function import(string $type, $source, $transformer = null, $validator = null, array &$errors = [])
    {
        $result = null;
        switch (strtolower($type)) {
            case 'csv':
                $result = (new CsvImporter())->import($source);
                break;
            case 'json':
                $result = (new JsonImporter())->import($source);
                break;
            case 'xml':
                $result = (new XmlImporter())->import($source);
                break;
            case 'sql':
                $result = (new SqlImporter())->import($source);
                break;
            case 'excel':
                $result = (new ExcelImporter())->import($source);
                break;
            case 'model':
                $result = (new ModelImporter())->import($source);
                break;
            case 'spatie':
                $result = (new SpatieDataImporter())->import($source);
                break;
            default:
                throw new \InvalidArgumentException("Unsupported import type: $type");
        }
        $transformer = $transformer ?: $this->transformer;
        $validator = $validator ?: $this->validator;
        foreach ($result as $item) {
            $original = $item;
            if ($transformer) {
                $item = self::applyTransformer($transformer, $item);
            }
            if ($validator) {
                if (!self::applyValidator($validator, $item)) {
                    $errors[] = $original;
                    continue;
                }
            }
            yield $item;
        }
    }

    /**
     * Export data with optional transformer.
     *
     * @param string $type
     * @param iterable $data
     * @param mixed $target
     * @param callable|object|null $transformer
     * @return void
     */
    public function export(string $type, iterable $data, $target, $transformer = null)
    {
        $transformer = $transformer ?: $this->transformer;
        if ($transformer) {
            $data = array_map(function ($item) use ($transformer) {
                return self::applyTransformer($transformer, $item);
            }, is_array($data) ? $data : iterator_to_array($data));
        }
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

    /**
     * Apply a transformer to a data item.
     *
     * @param callable|object $transformer
     * @param mixed $item
     * @return mixed
     */
    protected static function applyTransformer($transformer, $item)
    {
        if (is_callable($transformer)) {
            return $transformer($item);
        } elseif (is_object($transformer) && method_exists($transformer, 'transform')) {
            return $transformer->transform($item);
        }
        throw new \InvalidArgumentException('Invalid transformer.');
    }

    /**
     * Apply a validator to a data item.
     *
     * @param callable|object $validator
     * @param mixed $item
     * @return bool
     */
    protected static function applyValidator($validator, $item): bool
    {
        if (is_callable($validator)) {
            return (bool)$validator($item);
        } elseif (is_object($validator) && method_exists($validator, 'validate')) {
            return (bool)$validator->validate($item);
        }
        throw new \InvalidArgumentException('Invalid validator.');
    }
} 