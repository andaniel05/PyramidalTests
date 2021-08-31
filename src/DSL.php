<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests;

use Closure;
use ThenLabs\ClassBuilder\Model\Method;
use ThenLabs\ClassBuilder\Model\Property;
use ThenLabs\PyramidalTests\Model\TestCaseModel;
use ThenLabs\PyramidalTests\Model\TestModel;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
abstract class DSL
{
    public static function testCase(string $title, Closure $closure, bool $resetOld = true): TestCaseModel
    {
        if ($resetOld) {
            $oldTestCaseModel = Record::getCurrentTestCaseModel();
        }

        $newTestCaseModel = new TestCaseModel($title, $closure);

        $parentClass = Record::getTestCaseClass();
        $newTestCaseModel->getClassBuilder()->extends($parentClass);

        $currentTestCaseModel = Record::getCurrentTestCaseModel();

        if ($currentTestCaseModel instanceof TestCaseModel) {
            $currentTestCaseModel->addChild($newTestCaseModel);
        } else {
            Record::addTestCaseModel($newTestCaseModel);
        }

        Record::setCurrentTestCaseModel($newTestCaseModel);

        call_user_func($closure);

        if ($resetOld) {
            Record::setCurrentTestCaseModel($oldTestCaseModel);
        }

        return $newTestCaseModel;
    }

    public static function setUpBeforeClass(Closure $closure, bool $invokeParents): void
    {
        $currentTestCaseModel = Record::getCurrentTestCaseModel();
        $currentTestCaseModel->setSetUpBeforeClassClosure($closure, $invokeParents);
    }

    public static function setUp(Closure $closure, bool $invokeParents): void
    {
        $currentTestCaseModel = Record::getCurrentTestCaseModel();
        $currentTestCaseModel->setSetUpClosure($closure, $invokeParents);
    }

    public static function test(string $title, Closure $closure): TestModel
    {
        $currentTestCaseModel = Record::getCurrentTestCaseModel();

        if (! $currentTestCaseModel instanceof TestCaseModel) {
            self::testCase('', function () {
            }, false);
            $currentTestCaseModel = Record::getCurrentTestCaseModel();
        }

        $newTestModel = new TestModel($title, $closure, uniqid('test'));

        $currentTestCaseModel->addChild($newTestModel);

        return $newTestModel;
    }

    public static function tearDown(Closure $closure, bool $invokeParents): void
    {
        $currentTestCaseModel = Record::getCurrentTestCaseModel();
        $currentTestCaseModel->setTearDownClosure($closure, $invokeParents);
    }

    public static function tearDownAfterClass(Closure $closure, bool $invokeParents): void
    {
        $currentTestCaseModel = Record::getCurrentTestCaseModel();
        $currentTestCaseModel->setTearDownAfterClassClosure($closure, $invokeParents);
    }

    public static function setTestCaseClass(string $testCaseClass): void
    {
        Record::setTestCaseClass($testCaseClass);
    }

    public static function macro(string $title, Closure $closure): void
    {
        $currentTestCaseModel = Record::getCurrentTestCaseModel();

        if ($currentTestCaseModel) {
            $currentTestCaseModel->setMacro($title, $closure);
        } else {
            Record::setGlobalMacro($title, $closure);
        }
    }

    public static function useMacro(string $title, Closure $extendClosure = null): void
    {
        $closure = null;
        $currentTestCaseModel = Record::getCurrentTestCaseModel();

        if ($currentTestCaseModel) {
            $aux = $currentTestCaseModel;

            while (! $closure) {
                $closure = $aux->getMacro($title);

                if ($closure) {
                    break;
                } else {
                    $aux = $aux->getParent();

                    if (! $aux) {
                        $closure = Record::getGlobalMacro($title);
                        break;
                    }
                }
            }
        } else {
            $closure = Record::getGlobalMacro($title);
        }

        if (! $closure instanceof Closure) {
            throw new Exception\MacroNotFoundException($title);
        }

        if ($extendClosure) {
            (function ($closure, $extendClosure) {
                $closure();
                $extendClosure();
            })($closure, $extendClosure);
        } else {
            $closure();
        }
    }

    public static function staticProperty(string $name, $value): Property
    {
        $currentTestCaseModel = Record::getCurrentTestCaseModel();
        $classBuilder = $currentTestCaseModel->getClassBuilder();

        $propery = $classBuilder->addProperty($name)
            ->setStatic(true)
            ->setValue($value)
        ;

        return $propery;
    }

    public static function property(string $name, $value): Property
    {
        $currentTestCaseModel = Record::getCurrentTestCaseModel();
        $classBuilder = $currentTestCaseModel->getClassBuilder();

        $propery = $classBuilder->addProperty($name)
            ->setValue($value)
        ;

        return $propery;
    }

    public static function staticMethod(string $name, Closure $closure): Method
    {
        $currentTestCaseModel = Record::getCurrentTestCaseModel();
        $classBuilder = $currentTestCaseModel->getClassBuilder();

        $method = $classBuilder->addMethod($name)
            ->setStatic(true)
            ->setClosure($closure)
        ;

        return $method;
    }

    public static function method(string $name, Closure $closure): Method
    {
        $currentTestCaseModel = Record::getCurrentTestCaseModel();
        $classBuilder = $currentTestCaseModel->getClassBuilder();

        $method = $classBuilder->addMethod($name)
            ->setClosure($closure)
        ;

        return $method;
    }
}
