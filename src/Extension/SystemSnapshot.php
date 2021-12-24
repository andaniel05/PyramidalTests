<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Extension;

use Andaniel05\TestUtils\Asserts as TestUtilsAsserts;
use PHPUnit\Runner\AfterTestHook;
use PHPUnit\Runner\BeforeTestHook;
use ReflectionClass;
use ThenLabs\PyramidalTests\Extension\SystemSnapshot\ReaderInterface;
use ThenLabs\PyramidalTests\Extension\SystemSnapshot\SystemSnapshotInterface;
use ThenLabs\PyramidalTests\Model\Record;
use ThenLabs\PyramidalTests\Model\TestCaseModel;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class SystemSnapshot implements BeforeTestHook, AfterTestHook
{
    /**
     * @var array
     */
    protected static $before = [];

    /**
     * @var array
     */
    protected static $after = [];

    /**
     * @var array
     */
    protected static $expectations = [];

    /**
     * @var array<string, array>
     */
    protected static $executedTests = [];

    /**
     * @var array<string, ReaderInterface>
     */
    protected static $readers = [];

    public static function addReader(string $key, ReaderInterface $reader): void
    {
        static::$readers[$key] = $reader;
    }

    public static function getSnapshot(): array
    {
        $result = [];

        foreach (static::$readers as $key => $reader) {
            $result[$key] = $reader->getSnapshot();
        }

        return $result;
    }

    protected function getTestCaseModelDataFromTestName(string $testName): ?array
    {
        [$className, $methodName] = explode('::', $testName);

        foreach (Record::getAllTestCaseModels() as $testCaseModel) {
            if ($className === $testCaseModel->getClassBuilder()->getFCQN()) {
                return [$testCaseModel, $className, $methodName];
            } else {
                foreach ($testCaseModel->children() as $child) {
                    if ($child instanceof TestCaseModel &&
                        $className === $child->getClassBuilder()->getFCQN()
                    ) {
                        return [$child, $className, $methodName];
                    }
                }
            }
        }

        return null;
    }

    protected function implementsSystemSnapshotInterface(TestCaseModel $testCaseModel): bool
    {
        $class = new ReflectionClass($testCaseModel->getClassBuilder()->getFCQN());

        return $class->implementsInterface(SystemSnapshotInterface::class);
    }

    public static function addDiffExpectation(string $className, array $expectations): void
    {
        if (! isset(static::$expectations[$className])) {
            static::$expectations[$className] = [];
        }

        static::$expectations[$className] = array_merge_recursive(
            static::$expectations[$className],
            $expectations
        );
    }

    public function executeBeforeTest(string $test): void
    {
        [$testCaseModel, $className] = $this->getTestCaseModelDataFromTestName($test);

        if (! $testCaseModel ||
            ! $this->implementsSystemSnapshotInterface($testCaseModel)
        ) {
            return;
        }

        if (! isset(static::$executedTests[$className]) ||
            empty(static::$executedTests[$className])
        ) {
            static::$before[$className] = static::getSnapshot();
        }
    }

    public function executeAfterTest(string $test, float $time): void
    {
        [$testCaseModel, $className, $methodName] = $this->getTestCaseModelDataFromTestName($test);

        if (! $testCaseModel ||
            ! $this->implementsSystemSnapshotInterface($testCaseModel)
        ) {
            return;
        }

        if (! isset(static::$executedTests[$className])) {
            static::$executedTests[$className] = [];
        }

        static::$executedTests[$className][] = $methodName;

        $totalOfExecutedTests = count(static::$executedTests[$className]);
        $totalOfTests = count($testCaseModel->getRootTestModels());

        if ($totalOfExecutedTests === $totalOfTests) {
            static::$after[$className] = static::getSnapshot();

            TestUtilsAsserts::assertExpectedArrayDiff(
                static::$before[$className],
                static::$after[$className],
                static::$expectations[$className] ?? [],
            );
        }
    }
}
