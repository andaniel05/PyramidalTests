<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Decorator;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 * @abstract
 */
abstract class DecoratorsRegistry
{
    /**
     * @var AbstractDecorator[]
     */
    protected static $globals = [];

    /**
     * @var array<string, AbstractDecorator[]>
     */
    protected static $registry = [];

    public static function getGlobal(string $name): ?AbstractDecorator
    {
        return self::$globals[$name] ?? null;
    }

    public static function getGlobals(string $name): array
    {
        return self::$globals;
    }

    public static function registerGlobal(string $name, AbstractDecorator $decorator): void
    {
        self::$globals[$name] = $decorator;
    }

    public static function register(string $class, string $name, AbstractDecorator $decorator): void
    {
        if (! array_key_exists($class, self::$registry)) {
            self::$registry[$class] = [];
        }

        self::$registry[$class][$name] = $decorator;
    }

    public static function getForClass(string $class, string $name): ?AbstractDecorator
    {
        return self::$registry[$class][$name] ?? null;
    }
}
