<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Model\Decorator;

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
}
