<?php

namespace DataManager\Contracts;

interface ExporterInterface
{
    /**
     * Export data to a target.
     *
     * @param iterable $data
     * @param mixed $target
     * @return void
     */
    public function export(iterable $data, $target): void;
} 