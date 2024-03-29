<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Model;

use Closure;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Exception;
use ReflectionClass;
use ReflectionMethod;
use ThenLabs\ClassBuilder\ClassBuilder;
use ThenLabs\Components\CompositeComponentInterface;
use ThenLabs\Components\CompositeComponentTrait;
use ThenLabs\PyramidalTests\Annotation\Decorator;
use ThenLabs\PyramidalTests\Annotation\ImportDecorators;
use ThenLabs\PyramidalTests\Decorator\AbstractDecorator;
use ThenLabs\PyramidalTests\Decorator\DecoratorsRegistry;
use ThenLabs\PyramidalTests\Decorator\PackageInterface as DecoratorPackageInterface;
use ThenLabs\PyramidalTests\Plugins\SystemSnapshots\Contract\SnapshotsPerTestCase;
use ThenLabs\PyramidalTests\Plugins\SystemSnapshots\SystemSnapshots;

AnnotationRegistry::registerFile(__DIR__.'/../Annotation/Decorator.php');
AnnotationRegistry::registerFile(__DIR__.'/../Annotation/ImportDecorators.php');

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class TestCaseModel extends AbstractModel implements CompositeComponentInterface
{
    use CompositeComponentTrait;

    /**
     * @var ClassBuilder
     */
    protected $classBuilder;

    /**
     * @var ClassBuilder
     */
    protected $baseClassBuilder;

    /**
     * @var Closure
     */
    protected $setUpBeforeClassClosure;

    /**
     * @var array<string, Closure>
     */
    protected $setUpBeforeClassDecorators = [];

    /**
     * @var string[]
     */
    protected $executedDecorators = [];

    /**
     * @var bool
     */
    protected $invokeParentInSetUpBeforeClass;

    /**
     * @var bool
     */
    protected $invokedSetUpBeforeClass = false;

    /**
     * @var Closure
     */
    protected $setUpClosure;

    /**
     * @var bool
     */
    protected $invokeParentInSetUp;

    /**
     * @var Closure
     */
    protected $tearDownClosure;

    /**
     * @var bool
     */
    protected $invokeParentInTearDown;

    /**
     * @var Closure
     */
    protected $tearDownAfterClassClosure;

    /**
     * @var array<string, Closure>
     */
    protected $tearDownAfterClassDecorators = [];

    /**
     * @var bool
     */
    protected $invokeParentInTearDownAfterClass;

    /**
     * @var bool
     */
    protected $invokedTearDownAfterClass = false;

    /**
     * @var array
     */
    protected $macros = [];

    public function __construct(string $title, Closure $closure)
    {
        parent::__construct($title, $closure);

        $this->baseClassBuilder = new ClassBuilder(uniqid('BaseClass'));
        $this->classBuilder = new ClassBuilder(uniqid('TestCase'));
    }

    public function getClassBuilder(): ClassBuilder
    {
        return $this->classBuilder;
    }

    public function getBaseClassBuilder(): ClassBuilder
    {
        return $this->baseClassBuilder;
    }

    public function buildClass(): void
    {
        $thisTestCaseModel = $this;

        foreach ($this->parents() as $parentTestCaseModel) {
            $parentBaseClassBuilder = $parentTestCaseModel->getBaseClassBuilder();

            if (! class_exists($parentBaseClassBuilder->getFCQN())) {
                $parentTestCaseModel->buildClass();
            }
        }

        if ($this->title) {
            $this->classBuilder->addComment("@testdox {$this->title}");
        }

        if ($this->hasSnapshotsPerTestCase()) {
            $this->setUpBeforeClassDecorators[] = function () {
                SystemSnapshots::$snapshots[static::class] = [
                    'before' => SystemSnapshots::getSnapshot(),
                    'after' => [],
                ];
            };

            $this->tearDownAfterClassDecorators[] = function () {
                SystemSnapshots::$snapshots[static::class]['after'] = SystemSnapshots::getSnapshot();
                SystemSnapshots::compareSnapshots(static::class);
            };
        }

        // setUpBeforeClass
        $setUpBeforeClassDecorators = $this->setUpBeforeClassDecorators;
        $currentSetUpBeforeClassClosure = $this->setUpBeforeClassClosure;

        if (count($setUpBeforeClassDecorators)) {
            $this->setUpBeforeClassClosure = function () use ($setUpBeforeClassDecorators, $currentSetUpBeforeClassClosure, $thisTestCaseModel) {
                parent::setUpBeforeClass();

                $testCaseClass = $thisTestCaseModel->getClassBuilder()->getFCQN();

                foreach ($setUpBeforeClassDecorators as $setUpBeforeClassDecorator) {
                    $setUpBeforeClassDecorator = Closure::bind(
                        $setUpBeforeClassDecorator,
                        null,
                        $testCaseClass
                    );

                    $setUpBeforeClassDecorator();
                }

                if ($currentSetUpBeforeClassClosure instanceof Closure) {
                    $currentSetUpBeforeClassClosure = Closure::bind(
                        $currentSetUpBeforeClassClosure,
                        null,
                        $testCaseClass
                    );

                    $currentSetUpBeforeClassClosure();
                }
            };
        }

        if ($this->setUpBeforeClassClosure instanceof Closure) {
            $setUpBeforeClassClosure = $this->setUpBeforeClassClosure;

            if (true === $this->invokeParentInSetUpBeforeClass) {
                $setUpBeforeClassClosure = function () use ($thisTestCaseModel) {
                    parent::setUpBeforeClass();

                    $closure = Closure::bind(
                        $thisTestCaseModel->getSetUpBeforeClassClosure(),
                        null,
                        static::class
                    );

                    $closure();
                };
            }

            $this->baseClassBuilder->addMethod('setUpBeforeClass')
                ->setStatic(true)
                ->setReturnType('void')
                ->setClosure($setUpBeforeClassClosure)
            ;
        }

        // setUp
        if ($this->setUpClosure instanceof Closure) {
            $setUpClosure = $this->setUpClosure;

            if (true === $this->invokeParentInSetUp) {
                $setUpClosure = function () use ($thisTestCaseModel) {
                    // parent::setUp() not works becouse the test case class inherit from base class and
                    // that causes infinit loop.
                    $parentClass = $thisTestCaseModel->getBaseClassBuilder()->getParentClass();
                    call_user_func([$parentClass, 'setUp']);

                    $thisTestCaseModel->getSetUpClosure()->call($this);
                };
            }

            $this->baseClassBuilder->addMethod('setUp')
                ->setReturnType('void')
                ->setClosure($setUpClosure)
            ;
        }

        // tests
        foreach ($this->getRootTestModels() as $testModel) {
            $method = $testModel->getMethodBuilder();
            $method->setClassBuilder($this->classBuilder);
            $method->addComment("@testdox {$testModel->getTitle()}");

            $this->classBuilder->addMember($method);
        }

        // tearDown
        if ($this->tearDownClosure instanceof Closure) {
            $tearDownClosure = $this->tearDownClosure;

            if (true === $this->invokeParentInTearDown) {
                $tearDownClosure = function () use ($thisTestCaseModel) {
                    // parent::tearDown() not works becouse the test case class inherit from base class and
                    // that causes infinit loop.
                    $parentClass = $thisTestCaseModel->getBaseClassBuilder()->getParentClass();
                    call_user_func([$parentClass, 'tearDown']);

                    $thisTestCaseModel->getTearDownClosure()->call($this);
                };
            }

            $this->baseClassBuilder->addMethod('tearDown')
                ->setReturnType('void')
                ->setClosure($tearDownClosure)
            ;
        }

        // tearDownAfterClass
        $tearDownAfterClassDecorators = $this->tearDownAfterClassDecorators;
        $currentTearDownAfterClassClosure = $this->tearDownAfterClassClosure;

        if (count($tearDownAfterClassDecorators)) {
            $this->tearDownAfterClassClosure = function () use ($tearDownAfterClassDecorators, $currentTearDownAfterClassClosure, $thisTestCaseModel) {
                $testCaseClass = $thisTestCaseModel->getClassBuilder()->getFCQN();

                foreach ($tearDownAfterClassDecorators as $tearDownAfterClassDecorator) {
                    $tearDownAfterClassDecorator = Closure::bind(
                        $tearDownAfterClassDecorator,
                        null,
                        $testCaseClass
                    );

                    $tearDownAfterClassDecorator();
                }

                if ($currentTearDownAfterClassClosure instanceof Closure) {
                    $currentTearDownAfterClassClosure = Closure::bind(
                        $currentTearDownAfterClassClosure,
                        null,
                        $testCaseClass
                    );

                    $currentTearDownAfterClassClosure();
                }
            };
        }

        if ($this->tearDownAfterClassClosure instanceof Closure) {
            $tearDownAfterClassClosure = $this->tearDownAfterClassClosure;

            if (true === $this->invokeParentInTearDownAfterClass) {
                $tearDownAfterClassClosure = function () use ($thisTestCaseModel) {
                    parent::tearDownAfterClass();

                    $closure = Closure::bind(
                        $thisTestCaseModel->getTearDownAfterClassClosure(),
                        null,
                        static::class
                    );

                    $closure();
                };
            }

            $this->baseClassBuilder->addMethod('tearDownAfterClass')
                ->setStatic(true)
                ->setReturnType('void')
                ->setClosure($tearDownAfterClassClosure)
            ;
        }

        $parents = $this->getParents();

        $parentClass = count($parents) ?
            $parents[0]->getBaseClassBuilder()->getFCQN() :
            $this->baseClassBuilder->getParentClass()
        ;

        $this->baseClassBuilder->extends($parentClass);
        $this->baseClassBuilder->install();

        $this->classBuilder->extends($this->baseClassBuilder->getFCQN());
        $this->classBuilder->install();
    }

    public function getRootTestModels(): array
    {
        $callback = function (AbstractModel $child) {
            return $child instanceof TestModel ? true : false;
        };

        return $this->findChilds($callback, false);
    }

    public function getRootTestCaseModels(): array
    {
        $callback = function (AbstractModel $child) {
            return $child instanceof self ? true : false;
        };

        return $this->findChilds($callback, false);
    }

    public function setSetUpBeforeClassClosure(Closure $closure, bool $invokeParents): void
    {
        $this->setUpBeforeClassClosure = $closure;
        $this->invokeParentInSetUpBeforeClass = $invokeParents;
    }

    public function getSetUpBeforeClassClosure(): ?Closure
    {
        return $this->setUpBeforeClassClosure;
    }

    public function setSetUpClosure(Closure $closure, bool $invokeParents): void
    {
        $this->setUpClosure = $closure;
        $this->invokeParentInSetUp = $invokeParents;
    }

    public function getSetUpClosure(): ?Closure
    {
        return $this->setUpClosure;
    }

    public function setTearDownClosure(Closure $closure, bool $invokeParents): void
    {
        $this->tearDownClosure = $closure;
        $this->invokeParentInTearDown = $invokeParents;
    }

    public function getTearDownClosure(): ?Closure
    {
        return $this->tearDownClosure;
    }

    public function setTearDownAfterClassClosure(Closure $closure, bool $invokeParents): void
    {
        $this->tearDownAfterClassClosure = $closure;
        $this->invokeParentInTearDownAfterClass = $invokeParents;
    }

    public function getTearDownAfterClassClosure(): ?Closure
    {
        return $this->tearDownAfterClassClosure;
    }

    public function setMacro(string $title, Closure $closure): void
    {
        $this->macros[$title] = $closure;
    }

    public function getMacro(string $title): ?Closure
    {
        return $this->macros[$title] ?? null;
    }

    public function setInvokedSetUpBeforeClass(bool $invokedSetUpBeforeClass): void
    {
        $this->invokedSetUpBeforeClass = $invokedSetUpBeforeClass;
    }

    public function getInvokedSetUpBeforeClass(): bool
    {
        return $this->invokedSetUpBeforeClass;
    }

    public function setInvokedTearDownAfterClass(bool $invokedTearDownAfterClass): void
    {
        $this->invokedTearDownAfterClass = $invokedTearDownAfterClass;
    }

    public function getInvokedTearDownAfterClass(): bool
    {
        return $this->invokedTearDownAfterClass;
    }

    public function __call($decoratorName, $arguments)
    {
        $thisTestCaseModel = $this;

        // check if exists a global decorator.
        $decorator = DecoratorsRegistry::getGlobal($decoratorName);

        // check if exists for the final test case class.
        if (null === $decorator) {
            $decorator = DecoratorsRegistry::getForClass(
                $this->classBuilder->getFCQN(),
                $decoratorName
            );
        }

        // check if exists for the base test case class and the rest of the parents.
        if (null === $decorator) {
            $baseClass = new ReflectionClass($this->baseClassBuilder->getParentClass());
            $decorator = DecoratorsRegistry::getForClass($baseClass->getName(), $decoratorName);

            while (null === $decorator) {
                $parentClass = isset($parentClass) ?
                    $parentClass->getParentClass() :
                    $baseClass->getParentClass()
                ;

                if ($parentClass) {
                    $decorator = DecoratorsRegistry::getForClass($parentClass->getName(), $decoratorName);
                } else {
                    break;
                }
            }
        }

        // check if exists as an annotated method.
        if (null === $decorator) {
            $reader = new AnnotationReader();

            foreach ($baseClass->getMethods() as $method) {
                try {
                    $decoratorAnnotation = $reader->getMethodAnnotation($method, Decorator::class);

                    if ($decoratorAnnotation &&
                        $decoratorName == $decoratorAnnotation->name
                    ) {
                        $dummy = $baseClass->newInstanceWithoutConstructor();
                        $decorator = $method->invoke($dummy);
                        break;
                    }
                } catch (Exception $exception) {
                }
            }
        }

        // load decorators from the ImportDecorators annotation.
        if (null === $decorator) {
            $importDecoratorsAnnotation = $reader->getClassAnnotation(
                $baseClass,
                ImportDecorators::class
            );

            if ($importDecoratorsAnnotation) {
                foreach ($importDecoratorsAnnotation->decorators as $decoratorsPackageClass) {
                    $callback = [$decoratorsPackageClass, 'getDecorators'];

                    if (is_callable($callback)) {
                        $decorators = call_user_func($callback);

                        foreach ($decorators as $name => $decoratorInstance) {
                            DecoratorsRegistry::register(
                                $baseClass->getName(),
                                $name,
                                $decoratorInstance
                            );

                            if ($name == $decoratorName) {
                                $decorator = $decoratorInstance;
                            }
                        }
                    }
                }
            }
        }

        // Check if exists a static method for use it as decorator.
        if (null === $decorator /* && $baseClass->hasMethod($decoratorName) */) {
            $auxClass = $baseClass;
            $method = null;

            while (null === $method) {
                if ($auxClass->hasMethod($decoratorName)) {
                    $method = $auxClass->getMethod($decoratorName);
                    break;
                } else {
                    $auxClass = $auxClass->getParentClass();

                    if (! $auxClass) {
                        break;
                    }
                }
            }

            if ($method instanceof ReflectionMethod &&
                $method->isStatic()
            ) {
                $decorator = new class($decoratorName, $arguments) extends AbstractDecorator {
                    public function __construct($methodName, $arguments)
                    {
                        $this->methodName = $methodName;
                        $this->arguments = $arguments;
                    }

                    public function getClosure(array $arguments): ?Closure
                    {
                        $methodName = $this->methodName;
                        $arguments = $this->arguments;

                        return function () use ($methodName, $arguments) {
                            call_user_func_array([static::class, $methodName], $arguments);
                        };
                    }
                };
            }
        }

        if (! $decorator) {
            throw new Exception("Decorator '{$decoratorName}' for test case '{$this->title}' is missing.");
        }

        $setUpBeforeClassDecorator = $decorator->getClosure($arguments);

        if ($setUpBeforeClassDecorator instanceof Closure) {
            $thisFCQN = $this->classBuilder->getFCQN();

            $argumentsList = [];
            foreach ($arguments as $value) {
                $argumentsList[] = var_export($value, true);
            }

            $setUpBeforeClassDecoratorTitle = $decoratorName.'('.implode(',', $argumentsList).')';

            $setUpBeforeClassDecorator = function () use ($setUpBeforeClassDecorator, $thisFCQN, $thisTestCaseModel, $setUpBeforeClassDecoratorTitle) {
                $setUpBeforeClassDecorator = Closure::bind(
                    $setUpBeforeClassDecorator,
                    null,
                    $thisFCQN
                );

                $setUpBeforeClassDecorator();

                $thisTestCaseModel->addExecutedDecorator($setUpBeforeClassDecoratorTitle);
            };

            $this->setUpBeforeClassDecorators[$setUpBeforeClassDecoratorTitle] = $setUpBeforeClassDecorator;
        }

        $result = $decorator->applyTo($this, $arguments);

        return $result ? $result : $this;
    }

    public function importDecorators(string $className): self
    {
        $class = new ReflectionClass($className);

        if ($class->implementsInterface(DecoratorPackageInterface::class)) {
            $decorators = call_user_func([$className, 'getDecorators']);

            foreach ($decorators as $name => $decorator) {
                DecoratorsRegistry::register(
                    $this->classBuilder->getFCQN(),
                    $name,
                    $decorator
                );
            }
        } else {
            $reader = new AnnotationReader();

            foreach ($class->getMethods() as $method) {
                try {
                    $decoratorAnnotation = $reader->getMethodAnnotation($method, Decorator::class);

                    if ($decoratorAnnotation) {
                        $decorator = call_user_func([$className, $method->getName()]);

                        DecoratorsRegistry::register(
                            $this->classBuilder->getFCQN(),
                            $decoratorAnnotation->name,
                            $decorator
                        );
                    }
                } catch (Exception $exception) {
                }
            }
        }

        return $this;
    }

    public function getExecutedDecorators(): array
    {
        return $this->executedDecorators;
    }

    public function addExecutedDecorator(string $title): void
    {
        $this->executedDecorators[] = $title;
    }

    public function hasSnapshotsPerTestCase(): bool
    {
        $aux = $this;

        while ($aux) {
            if (false !== array_search(SnapshotsPerTestCase::class, $aux->getClassBuilder()->getInterfaces())) {
                return true;
            }

            if (false !== array_search(SnapshotsPerTestCase::class, $aux->getBaseClassBuilder()->getInterfaces())) {
                return true;
            }

            $reflectionBaseClass = new ReflectionClass($aux->getBaseClassBuilder()->getParentClass());

            if ($reflectionBaseClass->implementsInterface(SnapshotsPerTestCase::class)) {
                return true;
            }

            $aux = $aux->getParent();
        }

        return false;
    }
}
