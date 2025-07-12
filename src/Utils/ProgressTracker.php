<?php

namespace DataManager\Utils;

class ProgressTracker
{
    protected int $total = 0;
    protected int $current = 0;

    public function __construct(int $total)
    {
        $this->total = $total;
        $this->current = 0;
    }

    public function advance(int $step = 1): void
    {
        $this->current += $step;
        $this->display();
    }

    public function display(): void
    {
        $percent = $this->total > 0 ? ($this->current / $this->total) * 100 : 0;
        echo sprintf("Progress: %d/%d (%.2f%%)\r", $this->current, $this->total, $percent);
    }

    public function finish(): void
    {
        $this->current = $this->total;
        $this->display();
        echo "\nDone.\n";
    }
} 