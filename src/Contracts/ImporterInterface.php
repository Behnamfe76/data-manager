<?php

namespace DataManager\Contracts;

interface ImporterInterface
{
    /**
     * Import data from a source.
     *
     * @param mixed $source
     * @return iterable
     */
    public function import($source): iterable;
} 