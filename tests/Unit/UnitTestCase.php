<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Tests\Unit;

use ThenLabs\PyramidalTests\Record;
use ThenLabs\PyramidalTests\Model\TestCaseModel;
use ThenLabs\PyramidalTests\Model\TestModel;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestSuite;
use PHPUnit\TextUI\TestRunner;
use Closure;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class UnitTestCase extends TestCase
{
    public function tearDown(): void
    {
        Record::clear();
        Registry::$data = [];
    }

    public function runTests(array $arguments = [], array $warnings = [], bool $exit = false): TestResult
    {
        ob_start();

        $runner = new TestRunner();
        $mainTestSuite = new TestSuite();

        foreach (Record::getAllTestCaseModels() as $testCaseModel) {
            if ($testCaseModel instanceof TestCaseModel) {
                $this->loadSuiteFromTestCaseModel($testCaseModel, $mainTestSuite);
            }
        }

        $result = $runner->doRun($mainTestSuite, $arguments, $warnings, $exit);

        if ($result->riskyCount() ||
            $result->errorCount() ||
            $result->skippedCount() ||
            $result->failureCount() ||
            $result->warningCount() ||
            $result->notImplementedCount()
        ) {
            ob_end_flush();
        } else {
            ob_end_clean();
        }

        return $result;
    }

    public function getTestNameFromClosure(Closure $closure): ?string
    {
        foreach (Record::getAllTestCaseModels() as $testCaseModel) {
            foreach ($testCaseModel->children() as $child) {
                if ($child instanceof TestModel &&
                    $closure === $child->getClosure()
                ) {
                    return $child->getTestName();
                }
            }
        }

        return null;
    }

    public function getTestModelFromClosure(Closure $closure): ?TestModel
    {
        foreach (Record::getAllTestCaseModels() as $testCaseModel) {
            foreach ($testCaseModel->children() as $child) {
                if ($child instanceof TestModel &&
                    $closure === $child->getClosure()
                ) {
                    return $child;
                }
            }
        }

        return null;
    }

    public function getTestCaseModelFromClosure(Closure $closure): ?TestCaseModel
    {
        foreach (Record::getAllTestCaseModels() as $testCaseModel) {
            if ($closure === $testCaseModel->getClosure()) {
                return $testCaseModel;
            }

            foreach ($testCaseModel->children() as $child) {
                if ($child instanceof TestCaseModel &&
                    $closure === $child->getClosure()
                ) {
                    return $child;
                }
            }
        }

        return null;
    }

    public function assertExpectedTotals(array $expectations, TestResult $result): void
    {
        $count = $expectations['success'] ?? 0;
        $riskyCount = $expectations['risky'] ?? 0;
        $errorCount = $expectations['error'] ?? 0;
        $skippedCount = $expectations['skipped'] ?? 0;
        $failureCount = $expectations['failure'] ?? 0;
        $warningCount = $expectations['warning'] ?? 0;
        $notImplementedCount = $expectations['notImplemented'] ?? 0;

        $this->assertEquals($riskyCount, $result->riskyCount());
        $this->assertEquals($errorCount, $result->errorCount());
        $this->assertEquals($skippedCount, $result->skippedCount());
        $this->assertEquals($failureCount, $result->failureCount());
        $this->assertEquals($warningCount, $result->warningCount());
        $this->assertEquals($notImplementedCount, $result->notImplementedCount());
        $this->assertEquals($count, $result->count());
    }

    public function assertTestWasExecuted(string $testName, TestResult $result): void
    {
        $passed = $result->passed();

        $this->assertArrayHasKey($testName, $passed);
    }

    private function loadSuiteFromTestCaseModel(TestCaseModel $testCaseModel, TestSuite $mainTestSuite): void
    {
        $testCaseModel->buildClass();

        if (count($testCaseModel->getRootTestModels())) {
            $newTestSuite = new TestSuite($testCaseModel->getClassBuilder()->getFCQN());
            $mainTestSuite->addTestSuite($newTestSuite);
        }

        foreach ($testCaseModel->getRootTestCaseModels() as $child) {
            if ($child instanceof TestCaseModel) {
                $this->loadSuiteFromTestCaseModel($child, $mainTestSuite);
            }
        }
    }
}
