<?php

namespace DataManager\Contracts;

interface DataTransformerInterface
{
    /**
     * Transform a single data item.
     *
     * @param mixed $item
     * @return mixed
     */
    public function transform($item);
} 