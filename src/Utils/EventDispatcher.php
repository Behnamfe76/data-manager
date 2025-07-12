<?php

namespace DataManager\Utils;

class EventDispatcher
{
    protected static array $listeners = [];

    /**
     * Register an event listener.
     *
     * @param string $event
     * @param callable $listener
     * @return void
     */
    public static function listen(string $event, callable $listener): void
    {
        self::$listeners[$event][] = $listener;
    }

    /**
     * Dispatch an event.
     *
     * @param string $event
     * @param mixed ...$payload
     * @return void
     */
    public static function dispatch(string $event, ...$payload): void
    {
        if (!empty(self::$listeners[$event])) {
            foreach (self::$listeners[$event] as $listener) {
                $listener(...$payload);
            }
        }
    }
} 