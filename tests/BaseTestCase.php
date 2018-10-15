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

namespace Andaniel05\PyramidalTests\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\TextUI\TestRunner;
use PHPUnit\TextUI\ResultPrinter;
use Andaniel05\PyramidalTests\Extension;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class BaseTestCase extends TestCase
{
    public function setUp()
    {
        $this->printer = new class extends ResultPrinter {
            public function write(string $buffer): void
            {
            }
        };

        $this->testRunner = new TestRunner;
        $this->testRunner->setPrinter($this->printer);
        Extension::setTestRunner($this->testRunner);
    }

    private function getExecutedGroups(TestResult $result): array
    {
        return [
            'passed' => $result->passed(),
            'errors' => $result->errors(),
            'failures' => $result->failures(),
            'warnings' => $result->warnings(),
            'risky' => $result->risky(),
        ];
    }

    public function assertTestWasExecuted(string $test, TestResult $result): void
    {
        $executedGroups = $this->getExecutedGroups($result);

        foreach ($executedGroups as $group) {
            foreach ($group as $testName => $data) {
                if ($data instanceof TestFailure) {
                    $testName = $data->getTestName();
                }

                if ($test === $testName) {
                    $this->assertTrue(true);
                    return;
                }
            }
        }

        throw new AssertionFailedError("The test '$test' was not executed");
    }

    public function assertTestWasNotExecuted(string $test, TestResult $result): void
    {
        $executedGroups = $this->getExecutedGroups($result);

        foreach ($executedGroups as $group) {
            foreach ($group as $testName => $data) {
                if ($data instanceof TestFailure) {
                    $testName = $data->getTestName();
                }

                if ($test === $testName) {
                    throw new AssertionFailedError("The test '$test' has been executed");
                }
            }
        }

        $this->assertTrue(true);
    }

    public function assertTestWasSuccessful(string $test, TestResult $result): void
    {
        $this->assertTestWasExecuted($test, $result);

        foreach ($result->passed() as $testName => $value) {
            if ($test === $testName) {
                $this->assertTrue(true);
                return;
            }
        }

        throw new AssertionFailedError("The test '$test' was not successful");
    }
}
