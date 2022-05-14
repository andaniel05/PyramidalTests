<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Plugins\SystemSnapshots;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
trait UsefulMethodsTrait
{
    public static function expectCreated(array $expectations, ?string $context = null): void
    {
        $context = $context ?? static::class;

        SystemSnapshots::expectCreated($expectations, $context);
    }

    public static function expectUpdated(array $expectations, ?string $context = null): void
    {
        $context = $context ?? static::class;

        SystemSnapshots::expectUpdated($expectations, $context);
    }

    public static function expectDeleted(array $expectations, ?string $context = null): void
    {
        $context = $context ?? static::class;

        SystemSnapshots::expectDeleted($expectations, $context);
    }
}
