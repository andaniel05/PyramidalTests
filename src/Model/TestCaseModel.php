<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Model;

use Closure;
use ThenLabs\ClassBuilder\ClassBuilder;
use ThenLabs\ClassBuilder\Model\Method;
use ThenLabs\Components\CompositeComponentTrait;
use ThenLabs\Components\CompositeComponentInterface;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class TestCaseModel extends AbstractModel implements CompositeComponentInterface
{
    use CompositeComponentTrait;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var ClassBuilder
     */
    protected $classBuilder;

    /**
     * @Closure
     */
    protected $setUpBeforeClassClosure;

    /**
     * @var bool
     */
    protected $invokeParentsInSetUpBeforeClass;

    /**
     * @Closure
     */
    protected $setUpClosure;

    /**
     * @var bool
     */
    protected $invokeParentsInSetUp;

    /**
     * @Closure
     */
    protected $tearDownClosure;

    /**
     * @var bool
     */
    protected $invokeParentsInTearDown;

    /**
     * @Closure
     */
    protected $tearDownAfterClassClosure;

    /**
     * @var bool
     */
    protected $invokeParentsInTearDownAfterClass;

    /**
     * @var array
     */
    protected $macros = [];

    public function __construct(string $title, Closure $closure)
    {
        parent::__construct($title, $closure);

        $this->classBuilder = new ClassBuilder(uniqid('TestCase'));
    }

    public function getClassBuilder(): ClassBuilder
    {
        return $this->classBuilder;
    }

    public function buildClass(): void
    {
        if ($this->title) {
            $this->classBuilder->addComment("@testdox {$this->title}");
        }

        // setUpBeforeClass
        if ($this->setUpBeforeClassClosure instanceof Closure) {
            $setUpBeforeClassClosure = $this->setUpBeforeClassClosure;

            if (true === $this->invokeParentsInSetUpBeforeClass) {
                $listOfParentsSetUpBeforeClassClosures = [];

                foreach ($this->parents() as $parentTestCaseModel) {
                    if ($parentTestCaseModel instanceof self &&
                        $parentSetUpBeforeClassClosure = $parentTestCaseModel->getSetUpBeforeClassClosure()
                    ) {
                        $listOfParentsSetUpBeforeClassClosures[] = $parentSetUpBeforeClassClosure;
                    } else {
                        break;
                    }
                }

                if (count($listOfParentsSetUpBeforeClassClosures)) {
                    $setUpBeforeClassClosure = function () use ($listOfParentsSetUpBeforeClassClosures, $setUpBeforeClassClosure) {
                        foreach (array_reverse($listOfParentsSetUpBeforeClassClosures) as $parentSetUpBeforeClassClosure) {
                            call_user_func($parentSetUpBeforeClassClosure);
                        }

                        call_user_func($setUpBeforeClassClosure);
                    };
                }
            }

            $this->classBuilder->addMethod('setUpBeforeClass')
                ->setStatic(true)
                ->setReturnType('void')
                ->setClosure($setUpBeforeClassClosure)
            ;
        }

        // setUp
        if ($this->setUpClosure instanceof Closure) {
            $setUpClosure = $this->setUpClosure;

            if (true === $this->invokeParentsInSetUp) {
                $listOfParentsSetUpClosures = [];

                foreach ($this->parents() as $parentTestCaseModel) {
                    if ($parentTestCaseModel instanceof self &&
                        $parentSetUpClosure = $parentTestCaseModel->getSetUpClosure()
                    ) {
                        $listOfParentsSetUpClosures[] = $parentSetUpClosure;
                    } else {
                        break;
                    }
                }

                if (count($listOfParentsSetUpClosures)) {
                    $setUpClosure = function () use ($listOfParentsSetUpClosures, $setUpClosure) {
                        /**
                         * In this context '$this' is the final test case instance.
                         */

                        foreach (array_reverse($listOfParentsSetUpClosures) as $parentSetUpClosure) {
                            $parentSetUpClosure->call($this);
                        }

                        $setUpClosure->call($this);
                    };
                }
            }

            $this->classBuilder->addMethod('setUp')
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

            if (true === $this->invokeParentsInTearDown) {
                $listOfParentsTearDownClosures = [];

                foreach ($this->parents() as $parentTestCaseModel) {
                    if ($parentTestCaseModel instanceof self &&
                        $parentTearDownClosure = $parentTestCaseModel->getTearDownClosure()
                    ) {
                        $listOfParentsTearDownClosures[] = $parentTearDownClosure;
                    } else {
                        break;
                    }
                }

                if (count($listOfParentsTearDownClosures)) {
                    $tearDownClosure = function () use ($listOfParentsTearDownClosures, $tearDownClosure) {
                        /**
                         * In this context '$this' is the final test case instance.
                         */

                        foreach (array_reverse($listOfParentsTearDownClosures) as $parentTearDownClosure) {
                            $parentTearDownClosure->call($this);
                        }

                        $tearDownClosure->call($this);
                    };
                }
            }

            $this->classBuilder->addMethod('tearDown')
                ->setReturnType('void')
                ->setClosure($tearDownClosure)
            ;
        }

        // tearDownAfterClass
        if ($this->tearDownAfterClassClosure instanceof Closure) {
            $tearDownAfterClassClosure = $this->tearDownAfterClassClosure;

            if (true === $this->invokeParentsInTearDownAfterClass) {
                $listOfParentsTearDownAfterClassClosures = [];

                foreach ($this->parents() as $parentTestCaseModel) {
                    if ($parentTestCaseModel instanceof self &&
                        $parentTearDownAfterClassClosure = $parentTestCaseModel->getTearDownAfterClassClosure()
                    ) {
                        $listOfParentsTearDownAfterClassClosures[] = $parentTearDownAfterClassClosure;
                    } else {
                        break;
                    }
                }

                if (count($listOfParentsTearDownAfterClassClosures)) {
                    $tearDownAfterClassClosure = function () use ($listOfParentsTearDownAfterClassClosures, $tearDownAfterClassClosure) {
                        foreach (array_reverse($listOfParentsTearDownAfterClassClosures) as $parentTearDownAfterClassClosure) {
                            call_user_func($parentTearDownAfterClassClosure);
                        }

                        call_user_func($tearDownAfterClassClosure);
                    };
                }
            }

            $this->classBuilder->addMethod('tearDownAfterClass')
                ->setStatic(true)
                ->setReturnType('void')
                ->setClosure($tearDownAfterClassClosure)
            ;
        }

        // methods
        foreach ($this->parents() as $parentTestCaseModel) {
            $parentClassBuilder = $parentTestCaseModel->getClassBuilder();

            foreach ($parentClassBuilder->getMembers() as $member) {
                if ($member instanceof Method &&
                    0 !== strpos($member->getName(), 'test') &&
                    null === $this->classBuilder->getMethod($member->getName())
                ) {
                    $method = clone $member;
                    $method->setClassBuilder($this->classBuilder);

                    $this->classBuilder->addMember($method);
                }
            }
        }

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
        $this->invokeParentsInSetUpBeforeClass = $invokeParents;
    }

    public function getSetUpBeforeClassClosure(): ?Closure
    {
        return $this->setUpBeforeClassClosure;
    }

    public function setSetUpClosure(Closure $closure, bool $invokeParents): void
    {
        $this->setUpClosure = $closure;
        $this->invokeParentsInSetUp = $invokeParents;
    }

    public function getSetUpClosure(): ?Closure
    {
        return $this->setUpClosure;
    }

    public function setTearDownClosure(Closure $closure, bool $invokeParents): void
    {
        $this->tearDownClosure = $closure;
        $this->invokeParentsInTearDown = $invokeParents;
    }

    public function getTearDownClosure(): ?Closure
    {
        return $this->tearDownClosure;
    }

    public function setTearDownAfterClassClosure(Closure $closure, bool $invokeParents): void
    {
        $this->tearDownAfterClassClosure = $closure;
        $this->invokeParentsInTearDownAfterClass = $invokeParents;
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
}