<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Extension\SystemSnapshot;

use Brick\VarExporter\VarExporter;
use PHPUnit\Framework\Assert as PHPUnitAssert;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 * @abstract
 */
abstract class Assert
{
    public static function assertExpectedArrayDiff(array $array1, array $array2, array $expects = []): void
    {
        $diff = self::arrayRecursiveDiff($array2, $array1);

        if (empty($diff)) {
            $diff = self::arrayRecursiveDiff($array1, $array2);
        }

        $callback = function (array $inputArray, array &$diff) use (&$callback) {
            foreach ($inputArray as $key => $value) {
                if (! array_key_exists($key, $diff)) {
                    throw new ExpectationFailedException("The difference has not the data '{$key}'.");
                }

                if (is_array($value)) {
                    $callback($value, $diff[$key]);

                    if (empty($diff[$key])) {
                        unset($diff[$key]);
                    }
                } else {
                    $unset = false;

                    if (is_callable($value)) {
                        $contraintCallback = $value;
                        if (true == call_user_func($contraintCallback, $diff[$key])) {
                            $unset = true;
                        }
                    } elseif ($value instanceof Constraint) {
                        $contraint = $value;
                        $unset = $contraint->evaluate($diff[$key]);
                    } elseif ($diff[$key] === $value) {
                        $unset = true;
                    }

                    if ($unset == true) {
                        unset($diff[$key]);
                    }
                }
            }
        };

        $callback($expects, $diff);

        if (empty($diff)) {
            PHPUnitAssert::assertTrue(true);
            return;
        }

        throw new AssertionFailedError(
            "\nUnexpected Array Diff:\n".VarExporter::export($diff)
        );
    }

    /**
     * @see https://www.php.net/manual/en/function.array-diff.php#91756
     */
    public static function arrayRecursiveDiff($aArray1, $aArray2)
    {
        $aReturn = array();

        foreach ($aArray1 as $mKey => $mValue) {
            if (array_key_exists($mKey, $aArray2)) {
                if (is_array($mValue)) {
                    $aRecursiveDiff = self::arrayRecursiveDiff($mValue, $aArray2[$mKey]);
                    if (count($aRecursiveDiff)) {
                        $aReturn[$mKey] = $aRecursiveDiff;
                    }
                } else {
                    if ($mValue != $aArray2[$mKey]) {
                        $aReturn[$mKey] = $mValue;
                    }
                }
            } else {
                $aReturn[$mKey] = $mValue;
            }
        }

        return $aReturn;
    }
}
