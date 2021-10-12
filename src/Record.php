<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests;

use Closure;
use ThenLabs\PyramidalTests\Model\TestCaseModel;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
abstract class Record
{
    /**
     * @var array
     */
    protected static $testCaseModels = [];

    /**
     * @var TestCaseModel|null
     */
    protected static $currentTestCaseModel;

    /**
     * @var string
     */
    protected static $testCaseClass = 'PHPUnit\Framework\TestCase';

    /**
     * @var array
     */
    protected static $globalMacros = [];

    public static function clear(): void
    {
        self::$testCaseModels = [];
        self::$currentTestCaseModel = null;
    }

    public static function getAllTestCaseModels(): array
    {
        return self::$testCaseModels;
    }

    public static function getCurrentTestCaseModel(): ?TestCaseModel
    {
        return self::$currentTestCaseModel;
    }

    public static function setCurrentTestCaseModel(?TestCaseModel $testCaseModel): void
    {
        self::$currentTestCaseModel = $testCaseModel;
    }

    public static function addTestCaseModel(TestCaseModel $testCaseModel): void
    {
        self::$testCaseModels[] = $testCaseModel;
    }

    public static function setTestCaseClass(string $testCaseClass): void
    {
        self::$testCaseClass = $testCaseClass;
    }

    public static function getTestCaseClass(): string
    {
        return self::$testCaseClass;
    }

    public static function setGlobalMacro(string $title, Closure $closure): void
    {
        self::$globalMacros[$title] = $closure;
    }

    public static function getGlobalMacro(string $title): ?Closure
    {
        return self::$globalMacros[$title] ?? null;
    }
}
