<?php

namespace Estouai\Weave\Blocks;

use Illuminate\Support\Collection;

class BlockRegistry
{
    // Value is either a Block class-string (resolved via the container) or a
    // Closure (config-declared blocks — see ConfigBlock — which take constructor
    // args `app()` can't guess).
    protected static array $blocks = [];

    public static function register(string $class): void
    {
        static::$blocks[app($class)->type()] = $class;
    }

    public static function registerConfig(string $type, array $config): void
    {
        static::$blocks[$type] = fn () => new ConfigBlock($type, $config);
    }

    public static function all(): Collection
    {
        return collect(static::$blocks)->map(fn ($entry) => static::resolve($entry));
    }

    public static function find(string $type): ?Block
    {
        return isset(static::$blocks[$type]) ? static::resolve(static::$blocks[$type]) : null;
    }

    protected static function resolve(string|\Closure $entry): Block
    {
        return $entry instanceof \Closure ? $entry() : app($entry);
    }
}
