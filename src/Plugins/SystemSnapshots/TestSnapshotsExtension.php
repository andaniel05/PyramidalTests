<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Plugins\SystemSnapshots;

use Brick\VarExporter\VarExporter;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Runner\AfterTestErrorHook;
use PHPUnit\Runner\AfterTestFailureHook;
use PHPUnit\Runner\AfterTestHook;
use PHPUnit\Runner\BeforeTestHook;
use ReflectionClass;
use ThenLabs\PyramidalTests\Plugins\SystemSnapshots\Driver\AbstractDriver;
use ThenLabs\SnapshotsComparator\Comparator as SnapshotsComparator;
use ThenLabs\SnapshotsComparator\ExpectationBuilder;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class TestSnapshotsExtension implements BeforeTestHook, AfterTestHook, AfterTestErrorHook, AfterTestFailureHook
{
    /**
     * @var array<string, AbstractDriver>
     */
    protected static $drivers = [];

    /**
     * @var array<string, array>
     */
    protected static $snapshots = [];

    /**
     * @var array<string, ExpectationBuilder>
     */
    protected static $expectations = [];

    /**
     * @var array<string>
     */
    protected static $ignoredTests = [];

    public function executeBeforeTest(string $testName): void
    {
        if (false == $this->requireSnapshots($testName)) {
            return;
        }

        static::$snapshots[$testName] = [
            'before' => static::getSnapshot(),
            'after' => [],
        ];
    }

    public function executeAfterTest(string $testName, float $time): void
    {
        if (false == $this->requireSnapshots($testName)) {
            return;
        }

        static::$snapshots[$testName]['after'] = static::getSnapshot();

        $snapshotsDiff = SnapshotsComparator::compare(
            static::$snapshots[$testName]['before'],
            static::$snapshots[$testName]['after'],
            static::getExpectationBuilderForTest($testName),
        );

        $unexpectations = $snapshotsDiff->getUnexpectations();

        if (!empty($unexpectations)) {
            throw new AssertionFailedError(
                "\nUnexpectations in snapshots:\n".VarExporter::export($unexpectations)
            );
        }

        Assert::assertTrue(true);
    }

    public function executeAfterTestError(string $test, string $message, float $time): void
    {
        static::$ignoredTests[] = $test;
    }

    public function executeAfterTestFailure(string $test, string $message, float $time): void
    {
        static::$ignoredTests[] = $test;
    }

    protected function requireSnapshots(string $testName): bool
    {
        if (in_array($testName, static::$ignoredTests)) {
            return false;
        }

        $testInfo = $this->getTestInfo($testName);
        $class = new ReflectionClass($testInfo['class']);

        return $class->isSubclassOf(SnapshotsPerTestInterface::class);
    }

    protected function getTestInfo(string $testName): array
    {
        [$class, $method] = explode('::', $testName);

        return compact('class', 'method');
    }

    public static function getSnapshot(): array
    {
        $result = [];

        foreach (static::$drivers as $name => $driver) {
            $result[$name] = $driver->getData();
        }

        return $result;
    }

    public static function addDriver(string $name, AbstractDriver $driver): void
    {
        static::$drivers[$name] = $driver;
    }

    public static function resetAll(): void
    {
        foreach (static::$drivers as $driver) {
            $driver->reset();
        }
    }

    public static function reset(string $driverName): void
    {
        $driver = static::$drivers[$driverName] ?? null;

        if ($driver instanceof AbstractDriver) {
            $driver->reset();
        }
    }

    public static function clearDrivers(): void
    {
        static::$drivers = [];
    }

    public static function clearSnapshots(): void
    {
        static::$snapshots = [];
    }

    public static function clearExpectations(): void
    {
        static::$expectations = [];
    }

    public static function expectSnapshotDiff(array $expectations, string $testName = null): void
    {
        if (null === $testName) {
            $registeredTestsWithExpectations = array_keys(static::$snapshots);
            $testName = array_pop($registeredTestsWithExpectations);
        }

        $expectationBuilder = static::getExpectationBuilderForTest($testName);

        if (array_key_exists('CREATED', $expectations)) {
            $expectationBuilder->expectCreated($expectations['CREATED']);
        }

        if (array_key_exists('UPDATED', $expectations)) {
            $expectationBuilder->expectUpdated($expectations['UPDATED']);
        }

        if (array_key_exists('DELETED', $expectations)) {
            $expectationBuilder->expectDeleted($expectations['DELETED']);
        }
    }

    protected static function getExpectationBuilderForTest(string $testName): ExpectationBuilder
    {
        if (! isset(static::$expectations[$testName])) {
            static::$expectations[$testName] = new ExpectationBuilder();
        }

        return static::$expectations[$testName];
    }
}
