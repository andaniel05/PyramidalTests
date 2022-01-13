<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\DSL;

use Closure;
use ReflectionFunction;
use ThenLabs\PyramidalTests\Exception\MacroNotFoundException;
use ThenLabs\PyramidalTests\Model\Record;
use ThenLabs\PyramidalTests\Model\TestCaseModel;
use ThenLabs\PyramidalTests\Model\TestModel;
use ThenLabs\PyramidalTests\Utils\Proxy;
use TypeError;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 * @abstract
 */
abstract class DSL
{
    public static function testCase($firstArgument = '', Closure $secondArgument = null, bool $resetOld = true, TestCaseModel $currentTestCaseModel = null): TestCaseModel
    {
        $title = '';
        $closure = null;

        if (is_string($firstArgument)) {
            $title = $firstArgument;
            $closure = $secondArgument instanceof Closure ? $secondArgument : function () {
            };
        } elseif ($firstArgument instanceof Closure) {
            $closure = $firstArgument;
        } else {
            throw new TypeError('Invalid arguments.');
        }

        if ($resetOld) {
            $oldTestCaseModel = Record::getCurrentTestCaseModel();
        }

        if (! $title) {
            $title = self::getTitleForRootTestCaseModelFromClosure($closure);
        }

        $newTestCaseModel = new TestCaseModel($title, $closure);
        $newTestCaseModel->getBaseClassBuilder()->extends(Record::getTestCaseClass());

        $currentTestCaseModel = $currentTestCaseModel ?
            $currentTestCaseModel :
            Record::getCurrentTestCaseModel()
        ;

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

    public static function setUpBeforeClass(Closure $closure, bool $invokeParents, TestCaseModel $currentTestCaseModel = null): TestCaseModel
    {
        $currentTestCaseModel = $currentTestCaseModel ?
            $currentTestCaseModel :
            Record::getCurrentTestCaseModel()
        ;

        if (! $currentTestCaseModel instanceof TestCaseModel) {
            $currentTestCaseModel = self::createRootTestCaseModel(
                self::getTitleForRootTestCaseModelFromClosure($closure)
            );
        }

        $currentTestCaseModel->setSetUpBeforeClassClosure($closure, $invokeParents);

        return $currentTestCaseModel;
    }

    public static function setUpBeforeClassOnce(Closure $closure, bool $invokeParents, TestCaseModel $currentTestCaseModel = null): TestCaseModel
    {
        $currentTestCaseModel = $currentTestCaseModel ?
            $currentTestCaseModel :
            Record::getCurrentTestCaseModel()
        ;

        if (! $currentTestCaseModel instanceof TestCaseModel) {
            $currentTestCaseModel = self::createRootTestCaseModel(
                self::getTitleForRootTestCaseModelFromClosure($closure)
            );
        }

        $wrapperClosure = function () use ($closure, $currentTestCaseModel) {
            if ($currentTestCaseModel->getInvokedSetUpBeforeClass()) {
                return;
            }

            $closure = Closure::bind(
                $closure,
                null,
                $currentTestCaseModel->getBaseClassBuilder()->getFCQN()
            );

            $closure();
            $currentTestCaseModel->setInvokedSetUpBeforeClass(true);
        };

        $currentTestCaseModel->setSetUpBeforeClassClosure($wrapperClosure, $invokeParents);

        return $currentTestCaseModel;
    }

    public static function setUp(Closure $closure, bool $invokeParents, TestCaseModel $currentTestCaseModel = null): TestCaseModel
    {
        $currentTestCaseModel = $currentTestCaseModel ?
            $currentTestCaseModel :
            Record::getCurrentTestCaseModel()
        ;

        if (! $currentTestCaseModel instanceof TestCaseModel) {
            $currentTestCaseModel = self::createRootTestCaseModel(
                self::getTitleForRootTestCaseModelFromClosure($closure)
            );
        }

        $currentTestCaseModel->setSetUpClosure($closure, $invokeParents);

        return $currentTestCaseModel;
    }

    /**
     * @param string|Closure $title
     * @param Closure $closure
     * @return TestModel
     */
    public static function test($firstArgument, ?Closure $secondArgument, TestCaseModel $currentTestCaseModel = null): TestModel
    {
        if (is_string($firstArgument) && $secondArgument instanceof Closure) {
            $title = $firstArgument;
            $closure = $secondArgument;
        } elseif ($firstArgument instanceof Closure) {
            $title = '';
            $closure = $firstArgument;
        } else {
            throw new TypeError('Invalid arguments.');
        }

        $currentTestCaseModel = $currentTestCaseModel ?
            $currentTestCaseModel :
            Record::getCurrentTestCaseModel()
        ;

        if (! $currentTestCaseModel instanceof TestCaseModel) {
            $currentTestCaseModel = self::createRootTestCaseModel(
                self::getTitleForRootTestCaseModelFromClosure($closure)
            );
        }

        $totalOfCurrentTestModels = count($currentTestCaseModel->getRootTestModels());
        $id = $totalOfCurrentTestModels + 1;
        $methodName = 'test'.$id;

        if (! $title) {
            $reflectionFunction = new ReflectionFunction($closure);
            $line = ':'.$reflectionFunction->getStartLine();

            $title = "{$methodName} {$line}";
        }

        $newTestModel = new TestModel($title, $closure, $methodName);

        $currentTestCaseModel->addChild($newTestModel);

        return $newTestModel;
    }

    public static function tearDown(Closure $closure, bool $invokeParents, TestCaseModel $currentTestCaseModel = null): TestCaseModel
    {
        $currentTestCaseModel = $currentTestCaseModel ?
            $currentTestCaseModel :
            Record::getCurrentTestCaseModel()
        ;

        if (! $currentTestCaseModel instanceof TestCaseModel) {
            $currentTestCaseModel = self::createRootTestCaseModel(
                self::getTitleForRootTestCaseModelFromClosure($closure)
            );
        }

        $currentTestCaseModel->setTearDownClosure($closure, $invokeParents);

        return $currentTestCaseModel;
    }

    public static function tearDownAfterClass(Closure $closure, bool $invokeParents, TestCaseModel $currentTestCaseModel = null): TestCaseModel
    {
        $currentTestCaseModel = $currentTestCaseModel ?
            $currentTestCaseModel :
            Record::getCurrentTestCaseModel()
        ;

        if (! $currentTestCaseModel instanceof TestCaseModel) {
            $currentTestCaseModel = self::createRootTestCaseModel(
                self::getTitleForRootTestCaseModelFromClosure($closure)
            );
        }

        $currentTestCaseModel->setTearDownAfterClassClosure($closure, $invokeParents);

        return $currentTestCaseModel;
    }

    public static function tearDownAfterClassOnce(Closure $closure, bool $invokeParents, TestCaseModel $currentTestCaseModel = null): TestCaseModel
    {
        $currentTestCaseModel = $currentTestCaseModel ?
            $currentTestCaseModel :
            Record::getCurrentTestCaseModel()
        ;

        if (! $currentTestCaseModel instanceof TestCaseModel) {
            $currentTestCaseModel = self::createRootTestCaseModel(
                self::getTitleForRootTestCaseModelFromClosure($closure)
            );
        }

        $wrapperClosure = function () use ($closure, $currentTestCaseModel) {
            if ($currentTestCaseModel->getInvokedTearDownAfterClass()) {
                return;
            }

            $closure = Closure::bind(
                $closure,
                null,
                $currentTestCaseModel->getBaseClassBuilder()->getFCQN()
            );

            $closure();
            $currentTestCaseModel->setInvokedTearDownAfterClass(true);
        };

        $currentTestCaseModel->setTearDownAfterClassClosure($wrapperClosure, $invokeParents);

        return $currentTestCaseModel;
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

    public static function useMacro(string $title, Closure $extendClosure = null, TestCaseModel $currentTestCaseModel = null): TestCaseModel
    {
        $closure = null;

        $currentTestCaseModel = $currentTestCaseModel ?
            $currentTestCaseModel :
            Record::getCurrentTestCaseModel()
        ;

        if (! $currentTestCaseModel instanceof TestCaseModel) {
            $backtrace = debug_backtrace();
            $step = $backtrace[1];
            $titleOfParent = static::getRelativePath($step['file']);

            $currentTestCaseModel = self::createRootTestCaseModel($titleOfParent);
        }

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
            throw new MacroNotFoundException($title);
        }

        if ($extendClosure) {
            (function ($closure, $extendClosure) {
                $closure();
                $extendClosure();
            })($closure, $extendClosure);
        } else {
            $closure($extendClosure ?? function () {
            });
        }

        return $currentTestCaseModel;
    }

    public static function staticProperty(string $name, $value, TestCaseModel $currentTestCaseModel = null): Proxy
    {
        $currentTestCaseModel = $currentTestCaseModel ?
            $currentTestCaseModel :
            Record::getCurrentTestCaseModel()
        ;

        if (! $currentTestCaseModel instanceof TestCaseModel) {
            $backtrace = debug_backtrace();
            $step = $backtrace[1];
            $titleOfParent = static::getRelativePath($step['file']);

            $currentTestCaseModel = self::createRootTestCaseModel($titleOfParent);
        }

        $baseClassBuilder = $currentTestCaseModel->getBaseClassBuilder();

        $propery = $baseClassBuilder->addProperty($name)
            ->setStatic(true)
            ->setValue($value)
        ;

        return new Proxy([$propery, $currentTestCaseModel]);
    }

    public static function property(string $name, $value, TestCaseModel $currentTestCaseModel = null): Proxy
    {
        $currentTestCaseModel = $currentTestCaseModel ?
            $currentTestCaseModel :
            Record::getCurrentTestCaseModel()
        ;

        if (! $currentTestCaseModel instanceof TestCaseModel) {
            $backtrace = debug_backtrace();
            $step = $backtrace[1];
            $titleOfParent = static::getRelativePath($step['file']);

            $currentTestCaseModel = self::createRootTestCaseModel($titleOfParent);
        }

        $baseClassBuilder = $currentTestCaseModel->getBaseClassBuilder();

        $propery = $baseClassBuilder->addProperty($name)
            ->setValue($value)
        ;

        return new Proxy([$propery, $currentTestCaseModel]);
    }

    public static function staticMethod(string $name, Closure $closure, TestCaseModel $currentTestCaseModel = null): Proxy
    {
        $currentTestCaseModel = $currentTestCaseModel ?
            $currentTestCaseModel :
            Record::getCurrentTestCaseModel()
        ;

        if (! $currentTestCaseModel instanceof TestCaseModel) {
            $currentTestCaseModel = self::createRootTestCaseModel(
                self::getTitleForRootTestCaseModelFromClosure($closure)
            );
        }

        $baseClassBuilder = $currentTestCaseModel->getBaseClassBuilder();

        $method = $baseClassBuilder->addMethod($name)
            ->setStatic(true)
            ->setClosure($closure)
        ;

        return new Proxy([$method, $currentTestCaseModel]);
    }

    public static function method(string $name, Closure $closure, TestCaseModel $currentTestCaseModel = null): Proxy
    {
        $currentTestCaseModel = $currentTestCaseModel ?
            $currentTestCaseModel :
            Record::getCurrentTestCaseModel()
        ;

        if (! $currentTestCaseModel instanceof TestCaseModel) {
            $currentTestCaseModel = self::createRootTestCaseModel(
                self::getTitleForRootTestCaseModelFromClosure($closure)
            );
        }

        $baseClassBuilder = $currentTestCaseModel->getBaseClassBuilder();

        $method = $baseClassBuilder->addMethod($name)
            ->setClosure($closure)
        ;

        return new Proxy([$method, $currentTestCaseModel]);
    }

    public static function useTrait(string $trait, array $definitions = [], TestCaseModel $currentTestCaseModel = null): TestCaseModel
    {
        $currentTestCaseModel = $currentTestCaseModel ?
            $currentTestCaseModel :
            Record::getCurrentTestCaseModel()
        ;

        if (! $currentTestCaseModel instanceof TestCaseModel) {
            $backtrace = debug_backtrace();
            $step = $backtrace[1];
            $titleOfParent = static::getRelativePath($step['file']);

            $currentTestCaseModel = self::createRootTestCaseModel($titleOfParent);
        }

        $baseClassBuilder = $currentTestCaseModel->getBaseClassBuilder();

        $baseClassBuilder->use($trait, $definitions);

        return $currentTestCaseModel;
    }

    public static function createRootTestCaseModel(string $title): TestCaseModel
    {
        self::testCase($title, function () {
        }, false);

        return Record::getCurrentTestCaseModel();
    }

    public static function getTitleForRootTestCaseModelFromClosure(Closure $closure): string
    {
        $reflectionFunction = new ReflectionFunction($closure);
        return static::getRelativePath($reflectionFunction->getFileName());
    }

    public static function getRelativePath(string $fileName): string
    {
        $cwd = getcwd().'/';
        $result = str_replace($cwd, '', $fileName);

        return $result;
    }
}
