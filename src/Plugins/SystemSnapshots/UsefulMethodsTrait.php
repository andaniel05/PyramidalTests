<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Plugins\SystemSnapshots;

use DateTime;
use DateTimeInterface;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
trait UsefulMethodsTrait
{
    public static function expectCreated(array $expectations, $context = null): void
    {
        $context = static::getRightContext($context);

        SystemSnapshots::expectCreated($expectations, $context);
    }

    public static function expectUpdated(array $expectations, $context = null): void
    {
        $context = static::getRightContext($context);

        SystemSnapshots::expectUpdated($expectations, $context);
    }

    public static function expectDeleted(array $expectations, $context = null): void
    {
        $context = static::getRightContext($context);

        SystemSnapshots::expectDeleted($expectations, $context);
    }

    public static function anyValue(): callable
    {
        return function () {
            return true;
        };
    }

    public static function nearMomentTo(DateTimeInterface $reference, int $minDiff = 3)
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

    public static function getRightContext($context): string
    {
        if (! $context) {
            return static::class;
        }

        if (is_string($context)) {
            return $context;
        }

        if ($context instanceof TestCase) {
            return get_class($context).'::'.$context->getName();
        }

        throw new Exception('Invalid context.');
    }
}
