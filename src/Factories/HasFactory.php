<?php

namespace Buan\Testing\Factories;

trait HasFactory
{
    /**
     * Get a new factory instance for the model.
     *
     * @param  callable|array|int|null  $count
     * @param  callable|array  $state
     */
    public static function factory($count = null, $state = []): Factory
    {
        $factory = static::newFactory() ?? Factory::factoryForModel(static::class);

        return $factory
            ->count(is_numeric($count) ? $count : null)
            ->state(is_callable($count) || is_array($count) ? $count : $state);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): ?Factory
    {
        return null;
    }
}
