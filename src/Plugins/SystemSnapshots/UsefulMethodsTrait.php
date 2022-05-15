<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Plugins\SystemSnapshots;

use DateTime;

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

    public static function anyValue(): callable
    {
        return function () {
            return true;
        };
    }

    public static function nearMomentTo(DateTime $reference, int $minDiff = 3)
    {
        return function (string $value) use ($reference, $minDiff) {
            $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $value);

            if (!$dateTime instanceof DateTime) {
                return false;
            }

            $diff = $dateTime->getTimestamp() - $reference->getTimestamp();

            return abs($diff) <= $minDiff ? true : false;
        };
    }
}
