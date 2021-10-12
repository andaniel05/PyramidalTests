<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Model;

use Closure;
use ThenLabs\ClassBuilder\Model\Method;
use ThenLabs\Components\ComponentInterface;
use ThenLabs\Components\ComponentTrait;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class TestModel extends AbstractModel implements ComponentInterface
{
    use ComponentTrait;

    /**
     * @var Method
     */
    protected $method;

    public function __construct(string $title, Closure $closure, string $methodName)
    {
        parent::__construct($title, $closure);

        $this->method = new Method($methodName);
        $this->method->setClosure($closure);
    }

    public function getTestName(): string
    {
        $testCaseClass = $this->getParent()->getClassBuilder()->getFCQN();

        return "{$testCaseClass}::{$this->getMethodName()}";
    }

    public function getMethodName(): string
    {
        return $this->method->getName();
    }

    public function setMethodName(string $methodName): void
    {
        $this->method->setName($methodName);
    }

    public function getMethodBuilder(): Method
    {
        return $this->method;
    }
}
