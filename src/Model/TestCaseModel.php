<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Model;

use Closure;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Exception;
use PHPUnit\Framework\Assert;
use ReflectionClass;
use ThenLabs\ClassBuilder\ClassBuilder;
use ThenLabs\Components\CompositeComponentInterface;
use ThenLabs\Components\CompositeComponentTrait;
use ThenLabs\PyramidalTests\Annotation\Decorator;
use ThenLabs\PyramidalTests\DSL\DSL;
use ThenLabs\PyramidalTests\Model\Decorator\DecoratorsRegistry;

AnnotationRegistry::registerFile(__DIR__.'/../Annotation/Decorator.php');

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
     * @Closure
     */
    protected $setUpBeforeClassClosure;

    /**
     * @var bool
     */
    protected $invokeParentInSetUpBeforeClass;

    /**
     * @var bool
     */
    protected $invokedSetUpBeforeClass = false;

    /**
     * @Closure
     */
    protected $setUpClosure;

    /**
     * @var bool
     */
    protected $invokeParentInSetUp;

    /**
     * @Closure
     */
    protected $tearDownClosure;

    /**
     * @var bool
     */
    protected $invokeParentInTearDown;

    /**
     * @Closure
     */
    protected $tearDownAfterClassClosure;

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

        // setUpBeforeClass
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
        $decorator = DecoratorsRegistry::getGlobal($decoratorName);

        if (null === $decorator) {
            $decorator = DecoratorsRegistry::getForClass(
                $this->classBuilder->getFCQN(),
                $decoratorName
            );
        }

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

        if (null === $decorator) {
            $baseClass = new ReflectionClass($this->baseClassBuilder->getParentClass());
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

        if (! $decorator) {
            throw new Exception("Decorator '{$decoratorName}' for class '{$this->baseClassBuilder->getParentClass()}' is missing.");
        }

        $setUpBeforeClassDecorator = $decorator->getClosure();

        if ($setUpBeforeClassDecorator instanceof Closure) {
            $thisFCQN = $this->classBuilder->getFCQN();

            $setUpBeforeClassDecoratorWrapper = function () use ($setUpBeforeClassDecorator, $thisFCQN) {
                $setUpBeforeClassDecorator = Closure::bind(
                    $setUpBeforeClassDecorator,
                    null,
                    $thisFCQN
                );

                $setUpBeforeClassDecorator();

                Assert::assertTrue(true);
            };

            $argumentsList = [];
            foreach ($arguments as $value) {
                $argumentsList[] = var_export($value, true);
            }

            DSL::test(
                $decoratorName.'('.implode(',', $argumentsList).')',
                $setUpBeforeClassDecoratorWrapper,
                $this
            );
        }

        $result = $decorator->applyTo($this, $arguments);

        return $result ? $result : $this;
    }

    public function importDecorators(string $className): self
    {
        $class = new ReflectionClass($className);
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

        return $this;
    }
}
