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
use DataManager\Utils\EventDispatcher;

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
     * Import data with optional transformer and validator, with event hooks.
     *
     * Events: 'import.before', 'import.after', 'import.row', 'import.error'
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
        EventDispatcher::dispatch('import.before', $type, $source);
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
                    EventDispatcher::dispatch('import.error', $original);
                    continue;
                }
            }
            EventDispatcher::dispatch('import.row', $item);
            yield $item;
        }
        EventDispatcher::dispatch('import.after', $type, $source);
    }

    /**
     * Export data with optional transformer and event hooks.
     *
     * Events: 'export.before', 'export.after', 'export.row', 'export.error'
     *
     * @param string $type
     * @param iterable $data
     * @param mixed $target
     * @param callable|object|null $transformer
     * @return void
     */
    public function export(string $type, iterable $data, $target, $transformer = null)
    {
        EventDispatcher::dispatch('export.before', $type, $target);
        $transformer = $transformer ?: $this->transformer;
        if ($transformer) {
            $data = array_map(function ($item) use ($transformer) {
                $item = self::applyTransformer($transformer, $item);
                EventDispatcher::dispatch('export.row', $item);
                return $item;
            }, is_array($data) ? $data : iterator_to_array($data));
        } else {
            $data = is_array($data) ? $data : iterator_to_array($data);
            foreach ($data as $item) {
                EventDispatcher::dispatch('export.row', $item);
            }
        }
        try {
            switch (strtolower($type)) {
                case 'csv':
                    (new CsvExporter())->export($data, $target); break;
                case 'json':
                    (new JsonExporter())->export($data, $target); break;
                case 'xml':
                    (new XmlExporter())->export($data, $target); break;
                case 'sql':
                    (new SqlExporter())->export($data, $target); break;
                case 'excel':
                    (new ExcelExporter())->export($data, $target); break;
                case 'model':
                    (new ModelExporter())->export($data, $target); break;
                case 'spatie':
                    (new SpatieDataExporter())->export($data, $target); break;
                default:
                    throw new \InvalidArgumentException("Unsupported export type: $type");
            }
        } catch (\Throwable $e) {
            EventDispatcher::dispatch('export.error', $e);
            throw $e;
        }
        EventDispatcher::dispatch('export.after', $type, $target);
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