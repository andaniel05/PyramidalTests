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

namespace Andaniel05\PyramidalTests;

use PHPUnit\Runner\Hook;
use PHPUnit\TextUI\TestRunner;
use PHPUnit\Framework\TestSuite as PHPUnitTestSuite;
use PHPUnit\TextUI\Command;
use Andaniel05\PyramidalTests\Model\Record;
use ReflectionClass;
use PHPUnit\Util\TestDox\CliTestDoxPrinter;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Extension implements Hook
{
    protected static $testRunner;
    protected static $executed = false;

    public function __construct()
    {
        if (! static::$executed) {
            static::$executed = true;
            static::run();
        } else {
            return; // Prevent several executions of this extension.
        }
    }

    public static function setTestRunner(TestRunner $testRunner): void
    {
        static::$testRunner = $testRunner;
    }

    public static function run(array $arguments = [])
    {
        Record::buildClasses();

        $testSuite = new PHPUnitTestSuite;

        $traverse = function (array $testCases) use ($testSuite, &$traverse) {
            foreach ($testCases as $testCase) {
                if (! empty($testCase->getTests())) {
                    $testSuite->addTestSuite(
                        new ReflectionClass($testCase->getClassName())
                    );
                }

                $traverse($testCase->getTestCases());
            }
        };

        $traverse(Record::getTestCases());

        if (static::$testRunner instanceof TestRunner) {
            return static::$testRunner->doRun($testSuite, $arguments, false);
        } else {
            // Hack to get the argument array for the test runner.
            $command = new Command;
            $commandClass = new ReflectionClass($command);
            $handleArgumentsMethod = $commandClass->getMethod('handleArguments');
            $handleArgumentsMethod->getClosure($command)->call($command, $_SERVER['argv']);
            $arguments = (function () {
                return $this->arguments;
            })->call($command);

            $exit = (bool) ($_ENV['PYRAMIDAL_ONLY'] ?? false);

            $testRunner = new TestRunner;

            if (isset($arguments['printer']) && $arguments['printer'] == CliTestDoxPrinter::class) {
                $testRunner->setPrinter(new ResultPrinter);
            }

            $testRunner->doRun($testSuite, $arguments, $exit);

            static::printComments();
        }
    }

    public static function printComments(): void
    {
        $comments = <<<EOD
\n\n
=====================================================================
=                         PyramidalTests                            =
=       by Andy Daniel Navarro Taño <andaniel05@gmail.com>          =
=                                                                   =
= Top section corresponds to tests of the PyramidalTests Extension, =
= and the bottom to the rest of PHPUnit.                            =
=====================================================================
\n\n\n
EOD;

        fwrite(STDOUT, $comments);
    }
}
