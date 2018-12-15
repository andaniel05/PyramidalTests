<?php
declare(strict_types=1);

/*
    Copyright (C) <2018>  <Andy Daniel Navarro Taño>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Andaniel05\PyramidalTests\Model;

use Andaniel05\PyramidalTests\Exception\InvalidTestCaseClassException;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Closure;
use ReflectionClass;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 * @abstract
 */
abstract class Record
{
    protected static $currentTestCase;
    protected static $testCases = [];
    protected static $globalMacros = [];
    protected static $testCaseNamespace = 'Andaniel05\PyramidalTests\__Dynamic__';
    protected static $testCaseClass = '\PHPUnit\Framework\TestCase';

    public static function setTestCaseNamespace(string $namespace): void
    {
        static::$testCaseNamespace = $namespace;
    }

    public static function getTestCaseNamespace(): string
    {
        return static::$testCaseNamespace;
    }

    public static function setTestCaseClass(string $class): void
    {
        $reflection = new ReflectionClass($class);
        if ($reflection->getName() != PHPUnitTestCase::class &&
            ! $reflection->isSubclassOf('\PHPUnit\Framework\TestCase')) {
            throw new InvalidTestCaseClassException($class);
        }

        if ($class[0] == '\\') {
            static::$testCaseClass = $class;
        } else {
            static::$testCaseClass = '\\' . $class;
        }
    }

    public static function getTestCaseClass(): string
    {
        return static::$testCaseClass;
    }

    public static function testCases(): iterable
    {
        $traverse = function (array $testCases) use (&$traverse) {
            foreach ($testCases as $testCase) {
                yield $testCase;
                yield from $traverse($testCase->getTestCases());
            }

            return;
        };

        return $traverse(static::$testCases);
    }

    public static function buildClasses(): void
    {
        $script = '';

        foreach (static::testCases() as $testCase) {
            if (! class_exists($testCase->getClassName())) {
                $script .= "namespace {$testCase->getNamespace()};\n\n";
                $script .= $testCase->getBaseClassDefinition();
                $script .= $testCase->getClassDefinition();
                $script .= "\n\n";
            }
        }

        if ($script) {
            eval($script);
        }
    }

    public static function addTestCase(TestCase $testCase): void
    {
        static::$testCases[$testCase->getName()] = $testCase;
    }

    public static function getTestCases(): array
    {
        return static::$testCases;
    }

    public static function getTestCase(string $name): ?TestCase
    {
        return static::$testCases[$name] ?? null;
    }

    public static function getTestNameByClosure(Closure $closure): ?string
    {
        $traverse = function (array $testCases) use ($closure, &$traverse) {
            foreach ($testCases as $testCase) {
                foreach ($testCase->getTests() as $test) {
                    if ($test->getClosure() == $closure) {
                        return $test->getName();
                    }
                }

                return $traverse($testCase->getTestCases());
            }

            return null;
        };

        return $traverse(static::getTestCases());
    }

    public static function getTestCaseNameByClosure(Closure $closure): ?string
    {
        $traverse = function (array $testCases) use ($closure, &$traverse) {
            foreach ($testCases as $testCase) {
                if ($testCase->getClosure() == $closure) {
                    return $testCase->getName();
                } else {
                    return $traverse($testCase->getTestCases());
                }
            }

            return null;
        };

        return $traverse(static::getTestCases());
    }

    public static function getCurrentTestCase(): ?TestCase
    {
        return static::$currentTestCase;
    }

    public static function setCurrentTestCase(?TestCase $testCase): void
    {
        static::$currentTestCase = $testCase;
    }

    public static function addGlobalMacro(Macro $macro): void
    {
        static::$globalMacros[$macro->getDescription()] = $macro;
    }

    public static function getGlobalMacro(string $description): ?Macro
    {
        return static::$globalMacros[$description] ?? null;
    }
}
