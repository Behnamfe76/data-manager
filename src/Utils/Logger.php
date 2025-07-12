<?php

namespace DataManager\Utils;

class Logger
{
    /**
     * Log a message.
     *
     * @param string $message
     * @return void
     */
    public static function log(string $message): void
    {
        // Simple log to file (storage/logs/data-manager.log)
        $logFile = __DIR__ . '/../../storage/logs/data-manager.log';
        $date = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$date] $message\n", FILE_APPEND);
    }
} 