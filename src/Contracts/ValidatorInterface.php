<?php

namespace DataManager\Contracts;

interface ValidatorInterface
{
    /**
     * Validate a single data item.
     *
     * @param mixed $item
     * @return bool
     */
    public function validate($item): bool;
} 