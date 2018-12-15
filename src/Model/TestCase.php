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

use Andaniel05\PyramidalTests\Exception\DuplicatedTestException;
use Andaniel05\PyramidalTests\Exception\InvalidMethodNameException;
use Closure;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class TestCase extends Model
{
    protected $name;
    protected $parent;
    protected $topTestCaseClass;
    protected $namespace;
    protected $testCases = [];
    protected $setUpBeforeClass;
    protected $setUp;
    protected $tests = [];
    protected $methods = [];
    protected $macros = [];
    protected $staticMethods = [];
    protected $tearDown;
    protected $tearDownAfterClass;
    protected $invokeParentInSetUpBeforeClass = true;
    protected $invokeParentInSetUp = true;
    protected $invokeParentInTearDown = true;
    protected $invokeParentInTearDownAfterClass = true;

    public function addTestCase(TestCase $testCase): void
    {
        $this->testCases[$testCase->getName()] = $testCase;
    }

    public function getTestCases(): array
    {
        return $this->testCases;
    }

    public function getTestCase(string $name): ?TestCase
    {
        return $this->testCases[$name] ?? null;
    }

    public function addTest(Test $test): void
    {
        $testName = $test->getName();
        if (isset($this->tests[$testName])) {
            throw new DuplicatedTestException($this->getClassName(), $testName);
        }

        $this->tests[$testName] = $test;
    }

    public function getTests(): array
    {
        return $this->tests;
    }

    public function getTest(string $name): ?Test
    {
        return $this->tests[$name] ?? null;
    }

    public function getParent(): ?TestCase
    {
        return $this->parent;
    }

    public function setParent(?TestCase $parent): void
    {
        $this->parent = $parent;
    }

    public function getName(): string
    {
        if (! $this->name) {
            $this->name = str_replace(' ', '', ucwords($this->description));
            if (! preg_match(Model::FUNCTION_PATTERN, $this->name)) {
                $this->name = uniqid('TestCase');
            }
        }

        return $this->name;
    }

    public function getClassName(): string
    {
        return $this->getNamespace() . '\\' . $this->getName();
    }

    public function setSetUp(Closure $setUp): void
    {
        $this->setUp = $setUp;
    }

    public function getSetUp(): ?Closure
    {
        return $this->setUp;
    }

    public function setSetUpBeforeClass(Closure $setUpBeforeClass): void
    {
        $this->setUpBeforeClass = $setUpBeforeClass;
    }

    public function getSetUpBeforeClass(): ?Closure
    {
        return $this->setUpBeforeClass;
    }

    public function setTearDown(Closure $tearDown): void
    {
        $this->tearDown = $tearDown;
    }

    public function getTearDown(): ?Closure
    {
        return $this->tearDown;
    }

    public function setTearDownAfterClass(Closure $tearDownAfterClass): void
    {
        $this->tearDownAfterClass = $tearDownAfterClass;
    }

    public function getTearDownAfterClass(): ?Closure
    {
        return $this->tearDownAfterClass;
    }

    public function getParents(): array
    {
        $parents = [];

        $parent = $this->getParent();
        while ($parent instanceof self) {
            $parents[] = $parent;
            $parent = $parent->getParent();
        }

        return $parents;
    }

    public function getNamespace(): string
    {
        if (! $this->namespace) {
            $this->namespace = Record::getTestCaseNamespace();
        }

        return $this->namespace;
    }

    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    private function getDataForClassDefinition()
    {
        $getThisTestCase = '';

        $parents = array_reverse($this->getParents());
        if (empty($parents)) {
            $getThisTestCase = "\\Andaniel05\\PyramidalTests\\Model\\Record::getTestCase('{$this->getName()}')\n";
        } else {
            $topParent = array_shift($parents);
            $getThisTestCase = "\\Andaniel05\\PyramidalTests\\Model\\Record::getTestCase('{$topParent->getName()}')\n";
            foreach ($parents as $parent) {
                $getThisTestCase .= "->getTestCase('{$parent->getName()}')\n";
            }
            $getThisTestCase .= "->getTestCase('{$this->getName()}')\n";
        }

        $testMethods = null;

        foreach ($this->tests as $test) {
            $testMethods .= "
                /**
                 * @testdox {$test->getDescription()}
                 */
                public function {$test->getMethodName()}()
                {
                    {$getThisTestCase}
                        ->getTest('{$test->getName()}')
                        ->getClosure()
                        ->call(\$this);
                }
            ";
        }

        $topTestCaseClass = $this->topTestCaseClass ?? '\PHPUnit\Framework\TestCase';

        $parentClass = $this->getParent() ?
            "\\{$this->getParent()->getClassName()}Base" : $topTestCaseClass;

        $invokeParentInSetUpBeforeClass = $this->invokeParentInSetUpBeforeClass ?
            "parent::setUpBeforeClass();" : null;

        $invokeParentInSetUp = $this->invokeParentInSetUp ?
            "parent::setUp();" : null;

        $invokeParentInTearDown = $this->invokeParentInTearDown ?
            "parent::tearDown();" : null;

        $invokeParentInTearDownAfterClass = $this->invokeParentInTearDownAfterClass ?
            "parent::tearDownAfterClass();" : null;

        $getDataForMethod = function (Closure $closure) {
            $arguments = [];

            $reflectionClosure = new \ReflectionFunction($closure);
            foreach ($reflectionClosure->getParameters() as $parameter) {
                $arguments[] = $parameter->getName();
            }

            array_walk($arguments, function (&$name) {
                $name = "\$".$name;
            });
            $arguments = implode(',', $arguments);
            $callArguments = $arguments ? ",{$arguments}" : null;

            return compact('arguments', 'callArguments');
        };

        $staticMethods = '';
        foreach ($this->staticMethods as $methodName => $closure) {
            extract($getDataForMethod($closure));

            $staticMethods .= "
                public static function {$methodName}({$arguments})
                {
                    \$closure = {$getThisTestCase}
                        ->getStaticMethodClosure('{$methodName}')
                        ->bindTo(null, __CLASS__);

                    return call_user_func(\$closure {$callArguments});
                }
            ";
        }

        $methods = '';
        foreach ($this->methods as $methodName => $closure) {
            extract($getDataForMethod($closure));

            $methods .= "
                public function {$methodName}({$arguments})
                {
                    \$closure = {$getThisTestCase}
                        ->getMethodClosure('{$methodName}')
                        ->bindTo(\$this, __CLASS__);

                    return call_user_func(\$closure {$callArguments});
                }
            ";
        }

        return compact(
            'parentClass',
            'getThisTestCase',
            'invokeParentInSetUpBeforeClass',
            'invokeParentInSetUp',
            'invokeParentInTearDown',
            'invokeParentInTearDownAfterClass',
            'testMethods',
            'staticMethods',
            'methods'
        );
    }

    public function getBaseClassDefinition(): string
    {
        extract($this->getDataForClassDefinition());

        return "
            class {$this->getName()}Base extends {$parentClass}
            {
                public static function setUpBeforeClass()
                {
                    {$invokeParentInSetUpBeforeClass}

                    \$setUpBeforeClass = {$getThisTestCase}->getSetUpBeforeClass();
                    if (\$setUpBeforeClass instanceof \\Closure) {
                        call_user_func(
                            \\Closure::bind(\$setUpBeforeClass, null, self::class)
                        );
                    }
                }

                public function setUp()
                {
                    {$invokeParentInSetUp}

                    \$setUp = {$getThisTestCase}->getSetUp();
                    if (\$setUp instanceof \\Closure) {
                        \$setUp->call(\$this);
                    }
                }

                public function tearDown()
                {
                    {$invokeParentInTearDown}

                    \$tearDown = {$getThisTestCase}->getTearDown();
                    if (\$tearDown instanceof \\Closure) {
                        \$tearDown->call(\$this);
                    }
                }

                public static function tearDownAfterClass()
                {
                    {$invokeParentInTearDownAfterClass}

                    \$tearDownAfterClass = {$getThisTestCase}->getTearDownAfterClass();
                    if (\$tearDownAfterClass instanceof \\Closure) {
                        call_user_func(
                            \\Closure::bind(\$tearDownAfterClass, null, self::class)
                        );
                    }
                }

                {$staticMethods}

                {$methods}
            }
        ";
    }

    public function getClassDefinition(): string
    {
        extract($this->getDataForClassDefinition());

        $className = $this->getName();

        return "
            /**
             * @testdox {$this->getDescription()}
             */
            class {$className} extends {$className}Base
            {
            {$testMethods}
            }
        ";
    }

    public function setInvokeParentInSetUpBeforeClass(bool $invokeParent): void
    {
        $this->invokeParentInSetUpBeforeClass = $invokeParent;
    }

    public function setInvokeParentInSetUp(bool $invokeParent): void
    {
        $this->invokeParentInSetUp = $invokeParent;
    }

    public function setInvokeParentInTearDown(bool $invokeParent): void
    {
        $this->invokeParentInTearDown = $invokeParent;
    }

    public function setInvokeParentInTearDownAfterClass(bool $invokeParent): void
    {
        $this->invokeParentInTearDownAfterClass = $invokeParent;
    }

    public function setTopTestCaseClass(string $topTestCaseClass): void
    {
        $this->topTestCaseClass = $topTestCaseClass;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function createMethod(string $method, Closure $closure): void
    {
        if (! preg_match(Model::FUNCTION_PATTERN, $method)) {
            throw new InvalidMethodNameException($method);
        }

        $this->methods[$method] = $closure;
    }

    public function createStaticMethod(string $method, Closure $closure): void
    {
        if (! preg_match(Model::FUNCTION_PATTERN, $method)) {
            throw new InvalidMethodNameException($method);
        }

        $this->staticMethods[$method] = $closure;
    }

    public function getMethodClosure(string $methodName): ?Closure
    {
        return $this->methods[$methodName] ?? null;
    }

    public function getStaticMethodClosure(string $methodName): ?Closure
    {
        return $this->staticMethods[$methodName] ?? null;
    }

    public function addMacro(Macro $macro): void
    {
        $this->macros[$macro->getDescription()] = $macro;
    }

    public function getMacros(): array
    {
        return $this->macros;
    }

    public function getMacro(string $description): ?Macro
    {
        return $this->macros[$description] ?? null;
    }

    public function useMacro(Macro $macro): void
    {
        foreach ($macro->getTests() as $test) {
            $this->addTest($test);
        }

        foreach ($macro->getTestCases() as $testCase) {
            $this->addTestCase($testCase);
            $testCase->setParent($this);
            $testCase->setNamespace($this->getNamespace() . '\\' . $this->getName());
        }
    }
}
