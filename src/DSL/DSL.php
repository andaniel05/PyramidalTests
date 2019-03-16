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

namespace Andaniel05\PyramidalTests\DSL;

use Andaniel05\PyramidalTests\Model\Record;
use Andaniel05\PyramidalTests\Model\Test;
use Andaniel05\PyramidalTests\Model\Macro;
use Andaniel05\PyramidalTests\Model\TestCase;
use Andaniel05\PyramidalTests\Exception\MacroNotFoundException;
use Andaniel05\PyramidalTests\Exception\InvalidContextException;
use Closure;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 * @abstract
 */
abstract class DSL
{
    public static function setTestCaseNamespace(string $namespace): void
    {
        Record::setTestCaseNamespace($namespace);
    }

    public static function setTestCaseClass(string $class): void
    {
        Record::setTestCaseClass($class);
    }

    public static function testCase($description, Closure $closure = null): void
    {
        if ($description instanceof Closure) {
            $closure = $description;
            $description = uniqid('AnonymousTestCase');
        }

        $newTestCase = new TestCase($description, $closure);
        $newTestCase->setNamespace(Record::getTestCaseNamespace());
        $newTestCase->setTopTestCaseClass(Record::getTestCaseClass());

        $newTestCaseName = $newTestCase->getName();

        $currentTestCase = Record::getCurrentTestCase();
        if ($currentTestCase instanceof TestCase) {
            $oldTestCase = $currentTestCase->getTestCase($newTestCaseName);

            if (! $oldTestCase) {
                $oldTestCase = $currentTestCase->getTestCaseByDescription($description);
            }

            if ($oldTestCase) {
                $newTestCase = $oldTestCase;
            } else {
                $currentTestCase->addTestCase($newTestCase);
                $newTestCase->setParent($currentTestCase);
                $newTestCase->setNamespace(
                    $currentTestCase->getNamespace() . '\\' . $currentTestCase->getName()
                );
            }
        } else {
            Record::addTestCase($newTestCase);
        }

        Record::setCurrentTestCase($newTestCase);

        call_user_func($closure);

        Record::setCurrentTestCase($currentTestCase);
    }

    public static function setUpBeforeClass(Closure $closure, bool $invokeParent = true): void
    {
        $testCase = Record::getCurrentTestCase();

        if (! $testCase instanceof TestCase) {
            throw new InvalidContextException;
        }

        $testCase->setSetUpBeforeClass($closure);
        $testCase->setInvokeParentInSetUpBeforeClass($invokeParent);
    }

    public static function setUp(Closure $closure, bool $invokeParent = true): void
    {
        $testCase = Record::getCurrentTestCase();

        if (! $testCase instanceof TestCase) {
            throw new InvalidContextException;
        }

        $testCase->setSetUp($closure);
        $testCase->setInvokeParentInSetUp($invokeParent);
    }

    public static function test($description, ?Closure $closure = null): void
    {
        if ($description instanceof Closure) {
            $closure = $description;
            $description = uniqid('testAnonymous');
        }

        $currentTestCase = Record::getCurrentTestCase();
        if (! $currentTestCase) {
            $currentTestCase = new TestCase('DefaultTestCase');
            Record::addTestCase($currentTestCase);
            Record::setCurrentTestCase($currentTestCase);
        }

        $test = new Test($description, $closure);
        $currentTestCase->addTest($test);
    }

    public static function tearDown(Closure $closure, bool $invokeParent = true): void
    {
        $testCase = Record::getCurrentTestCase();

        if (! $testCase instanceof TestCase) {
            throw new InvalidContextException;
        }

        $testCase->setTearDown($closure);
        $testCase->setInvokeParentInTearDown($invokeParent);
    }

    public static function tearDownAfterClass(Closure $closure, bool $invokeParent = true): void
    {
        $testCase = Record::getCurrentTestCase();

        if (! $testCase instanceof TestCase) {
            throw new InvalidContextException;
        }

        $testCase->setTearDownAfterClass($closure);
        $testCase->setInvokeParentInTearDownAfterClass($invokeParent);
    }

    public static function createMethod(string $method, Closure $closure): void
    {
        $testCase = Record::getCurrentTestCase();

        if (! $testCase instanceof TestCase) {
            throw new InvalidContextException;
        }

        $testCase->createMethod($method, $closure);
    }

    public static function createStaticMethod(string $method, Closure $closure): void
    {
        $testCase = Record::getCurrentTestCase();

        if (! $testCase instanceof TestCase) {
            throw new InvalidContextException;
        }

        $testCase->createStaticMethod($method, $closure);
    }

    public static function createMacro(string $description, Closure $closure): void
    {
        $macro = new Macro($description, $closure);

        $currentTestCase = Record::getCurrentTestCase();

        if (! $currentTestCase) {
            Record::addGlobalMacro($macro);
        } else {
            $currentTestCase->addMacro($macro);
        }

        Record::setCurrentTestCase($macro);

        call_user_func($closure);

        Record::setCurrentTestCase($currentTestCase);
    }

    public static function useMacro(string $description): void
    {
        $testCase = Record::getCurrentTestCase();

        if (! $testCase instanceof TestCase) {
            throw new InvalidContextException;
        }

        $node = $testCase;
        while ($node) {
            $macro = $node->getMacro($description);
            if ($macro) {
                break;
            }

            $node = $node->getParent();
        }

        if (! $macro) {
            $macro = Record::getGlobalMacro($description);
        }

        if (! $macro) {
            throw new MacroNotFoundException($description);
        }

        $testCase->useMacro($macro);
    }

    public static function setUpBeforeClassOnce(Closure $closure, bool $invokeParent = true): void
    {
        $testCase = Record::getCurrentTestCase();

        if (! $testCase instanceof TestCase) {
            throw new InvalidContextException;
        }

        $testCase->setSetUpBeforeClass($closure, true);
        $testCase->setInvokeParentInSetUpBeforeClass($invokeParent);
    }

    public static function tearDownAfterClassOnce(Closure $closure, bool $invokeParent = true): void
    {
        $testCase = Record::getCurrentTestCase();

        if (! $testCase instanceof TestCase) {
            throw new InvalidContextException;
        }

        $testCase->setTearDownAfterClass($closure, true);
        $testCase->setInvokeParentInTearDownAfterClass($invokeParent);
    }
}
