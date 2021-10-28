<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Tests\Unit;

use Closure;
use ThenLabs\PyramidalTests\Annotation\Decorator;
use ThenLabs\PyramidalTests\Model\Decorator\AbstractDecorator;
use ThenLabs\PyramidalTests\Model\TestCaseModel;

class SomeClass
{
    public function doSomething()
    {
    }
}

class Observer
{
    public function update($argument)
    {
        // Do something.
    }

    public function reportError($errorCode, $errorMessage, Subject $subject)
    {
        // Do something
    }

    // Other methods.
}

class Subject
{
    protected $observers = [];
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function attach(Observer $observer)
    {
        $this->observers[] = $observer;
    }

    public function doSomething()
    {
        // Do something.
        // ...

        // Notify observers that we did something.
        $this->notify('something');
    }

    public function doSomethingBad()
    {
        foreach ($this->observers as $observer) {
            $observer->reportError(42, 'Something bad happened', $this);
        }
    }

    protected function notify($argument)
    {
        foreach ($this->observers as $observer) {
            $observer->update($argument);
        }
    }

    // Other methods.
}

class MyCustomTestCase extends \PHPUnit\Framework\TestCase
{
}

class MyDecoratorTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @Decorator(name="customDecorator")
     */
    public function myDecoratorMethod(): AbstractDecorator
    {
        return new class extends AbstractDecorator {
            public function getClosure(): ?Closure
            {
                return function () {
                    static::$myProperty = 10;
                };
            }

            public function applyTo(TestCaseModel $testCaseModel, array $arguments)
            {
                $classBuilder = $testCaseModel->getClassBuilder();
                $classBuilder->addProperty($arguments[0])->setStatic(true);
            }
        };
    }
}
