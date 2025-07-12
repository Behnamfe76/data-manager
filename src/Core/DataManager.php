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
use DataManager\Jobs\ImportJob;
use DataManager\Jobs\ExportJob;

class DataManager
{
    protected $transformer = null;
    protected $validator = null;
    protected $templates = [];

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
     * Register an import/export template.
     *
     * @param string $name
     * @param array $config (keys: type, transformer, validator, chunkSize, etc.)
     * @return $this
     */
    public function registerTemplate(string $name, array $config)
    {
        $this->templates[$name] = $config;
        return $this;
    }

    /**
     * Import using a registered template.
     *
     * @param string $templateName
     * @param mixed $source
     * @param array $errors (by reference)
     * @return iterable|array[]
     */
    public function importWithTemplate(string $templateName, $source, array &$errors = [])
    {
        if (!isset($this->templates[$templateName])) {
            throw new \InvalidArgumentException("Template not found: $templateName");
        }
        $tpl = $this->templates[$templateName];
        return $this->import(
            $tpl['type'],
            $source,
            $tpl['transformer'] ?? null,
            $tpl['validator'] ?? null,
            $errors,
            $tpl['chunkSize'] ?? null
        );
    }

    /**
     * Export using a registered template.
     *
     * @param string $templateName
     * @param iterable $data
     * @param mixed $target
     * @return void
     */
    public function exportWithTemplate(string $templateName, iterable $data, $target)
    {
        if (!isset($this->templates[$templateName])) {
            throw new \InvalidArgumentException("Template not found: $templateName");
        }
        $tpl = $this->templates[$templateName];
        $this->export(
            $tpl['type'],
            $data,
            $target,
            $tpl['transformer'] ?? null,
            $tpl['chunkSize'] ?? null
        );
    }

    /**
     * Detect the data format from file extension or content.
     *
     * @param mixed $source
     * @return string|null
     */
    protected function detectFormat($source): ?string
    {
        if (is_string($source)) {
            $ext = strtolower(pathinfo($source, PATHINFO_EXTENSION));
            switch ($ext) {
                case 'csv': return 'csv';
                case 'json': return 'json';
                case 'xml': return 'xml';
                case 'sql': return 'sql';
                case 'xlsx': case 'xls': return 'excel';
            }
        }
        return null;
    }

    /**
     * Import data with auto-detection of format if type is 'auto'.
     *
     * @param string $type
     * @param mixed $source
     * @param callable|object|null $transformer
     * @param callable|object|null $validator
     * @param array $errors (by reference)
     * @param int|null $chunkSize
     * @return iterable|array[]
     */
    public function import(string $type, $source, $transformer = null, $validator = null, array &$errors = [], int $chunkSize = null)
    {
        if ($type === 'auto') {
            $detected = $this->detectFormat($source);
            if (!$detected) {
                throw new \InvalidArgumentException('Could not auto-detect format for import.');
            }
            $type = $detected;
        }
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
        $buffer = [];
        $count = 0;
        $total = null;
        if (is_array($source) && isset($source[1]) && is_array($source[1])) {
            $total = count($source[1]);
        } elseif (is_string($source) && file_exists($source)) {
            $total = count(file($source));
        }
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
            $count++;
            if ($total) {
                $percent = ($count / $total) * 100;
                EventDispatcher::dispatch('import.progress', $count, $total, $percent);
            }
            if ($chunkSize) {
                $buffer[] = $item;
                if ($count % $chunkSize === 0) {
                    yield $buffer;
                    $buffer = [];
                }
            } else {
                yield $item;
            }
        }
        if ($chunkSize && !empty($buffer)) {
            yield $buffer;
        }
        EventDispatcher::dispatch('import.after', $type, $source);
    }

    /**
     * Export data with auto-detection of format if type is 'auto'.
     *
     * @param string $type
     * @param iterable $data
     * @param mixed $target
     * @param callable|object|null $transformer
     * @param int|null $chunkSize
     * @return void
     */
    public function export(string $type, iterable $data, $target, $transformer = null, int $chunkSize = null)
    {
        if ($type === 'auto') {
            $detected = $this->detectFormat($target);
            if (!$detected) {
                throw new \InvalidArgumentException('Could not auto-detect format for export.');
            }
            $type = $detected;
        }
        EventDispatcher::dispatch('export.before', $type, $target);
        $transformer = $transformer ?: $this->transformer;
        $data = is_array($data) ? $data : iterator_to_array($data);
        $total = count($data);
        if ($chunkSize) {
            $chunks = array_chunk($data, $chunkSize);
        } else {
            $chunks = [$data];
        }
        $count = 0;
        foreach ($chunks as $chunk) {
            if ($transformer) {
                $chunk = array_map(function ($item) use ($transformer) {
                    $item = self::applyTransformer($transformer, $item);
                    EventDispatcher::dispatch('export.row', $item);
                    return $item;
                }, $chunk);
            } else {
                foreach ($chunk as $item) {
                    EventDispatcher::dispatch('export.row', $item);
                }
            }
            $count += count($chunk);
            if ($total) {
                $percent = ($count / $total) * 100;
                EventDispatcher::dispatch('export.progress', $count, $total, $percent);
            }
            try {
                switch (strtolower($type)) {
                    case 'csv':
                        (new CsvExporter())->export($chunk, $target); break;
                    case 'json':
                        (new JsonExporter())->export($chunk, $target); break;
                    case 'xml':
                        (new XmlExporter())->export($chunk, $target); break;
                    case 'sql':
                        (new SqlExporter())->export($chunk, $target); break;
                    case 'excel':
                        (new ExcelExporter())->export($chunk, $target); break;
                    case 'model':
                        (new ModelExporter())->export($chunk, $target); break;
                    case 'spatie':
                        (new SpatieDataExporter())->export($chunk, $target); break;
                    default:
                        throw new \InvalidArgumentException("Unsupported export type: $type");
                }
            } catch (\Throwable $e) {
                EventDispatcher::dispatch('export.error', $e);
                throw $e;
            }
        }
        EventDispatcher::dispatch('export.after', $type, $target);
    }

    /**
     * Dispatch an import job to the queue.
     *
     * @param string $type
     * @param mixed $source
     * @param callable|object|null $transformer
     * @param callable|object|null $validator
     * @param int|null $chunkSize
     * @return void
     */
    public function queueImport($type, $source, $transformer = null, $validator = null, $chunkSize = null)
    {
        ImportJob::dispatch($type, $source, $transformer, $validator, $chunkSize);
    }

    /**
     * Dispatch an export job to the queue.
     *
     * @param string $type
     * @param iterable $data
     * @param mixed $target
     * @param callable|object|null $transformer
     * @param int|null $chunkSize
     * @return void
     */
    public function queueExport($type, $data, $target, $transformer = null, $chunkSize = null)
    {
        ExportJob::dispatch($type, $data, $target, $transformer, $chunkSize);
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