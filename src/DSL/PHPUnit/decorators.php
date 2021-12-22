<?php

use ThenLabs\PyramidalTests\Decorator\AbstractDecorator;
use ThenLabs\PyramidalTests\Decorator\DecoratorsRegistry;
use ThenLabs\PyramidalTests\DSL\DSL;
use ThenLabs\PyramidalTests\DSL\PHPUnit\Decorator\EndTestCaseDecorator;
use ThenLabs\PyramidalTests\DSL\PHPUnit\Decorator\PropertyDecorator;
use ThenLabs\PyramidalTests\DSL\PHPUnit\Decorator\SetUpBeforeClassDecorator;
use ThenLabs\PyramidalTests\DSL\PHPUnit\Decorator\SetUpBeforeClassOnceDecorator;
use ThenLabs\PyramidalTests\DSL\PHPUnit\Decorator\SetUpDecorator;
use ThenLabs\PyramidalTests\DSL\PHPUnit\Decorator\StaticMethodDecorator;
use ThenLabs\PyramidalTests\DSL\PHPUnit\Decorator\StaticPropertyDecorator;
use ThenLabs\PyramidalTests\DSL\PHPUnit\Decorator\TearDownAfterClassDecorator;
use ThenLabs\PyramidalTests\DSL\PHPUnit\Decorator\TearDownAfterClassOnceDecorator;
use ThenLabs\PyramidalTests\DSL\PHPUnit\Decorator\TearDownDecorator;
use ThenLabs\PyramidalTests\DSL\PHPUnit\Decorator\TestCaseDecorator;
use ThenLabs\PyramidalTests\DSL\PHPUnit\Decorator\TestDecorator;
use ThenLabs\PyramidalTests\Model\TestCaseModel;

DecoratorsRegistry::registerGlobal('testCase', new TestCaseDecorator());
DecoratorsRegistry::registerGlobal('endTestCase', new EndTestCaseDecorator());
DecoratorsRegistry::registerGlobal('setUpBeforeClass', new SetUpBeforeClassDecorator());
DecoratorsRegistry::registerGlobal('setUpBeforeClassOnce', new SetUpBeforeClassOnceDecorator());
DecoratorsRegistry::registerGlobal('setUp', new SetUpDecorator());
DecoratorsRegistry::registerGlobal('test', new TestDecorator());
DecoratorsRegistry::registerGlobal('tearDown', new TearDownDecorator());
DecoratorsRegistry::registerGlobal('tearDownAfterClass', new TearDownAfterClassDecorator());
DecoratorsRegistry::registerGlobal('tearDownAfterClassOnce', new TearDownAfterClassOnceDecorator());
DecoratorsRegistry::registerGlobal('staticProperty', new StaticPropertyDecorator());
DecoratorsRegistry::registerGlobal('property', new PropertyDecorator());
DecoratorsRegistry::registerGlobal('staticMethod', new StaticMethodDecorator());

DecoratorsRegistry::registerGlobal('method', new class extends AbstractDecorator {
    public function applyTo(TestCaseModel $testCaseModel, array $arguments)
    {
        return DSL::method($arguments[0], $arguments[1], $testCaseModel);
    }
});

DecoratorsRegistry::registerGlobal('useMacro', new class extends AbstractDecorator {
    public function applyTo(TestCaseModel $testCaseModel, array $arguments)
    {
        return DSL::useMacro($arguments[0], $arguments[1] ?? null, $testCaseModel);
    }
});

DecoratorsRegistry::registerGlobal('useTrait', new class extends AbstractDecorator {
    public function applyTo(TestCaseModel $testCaseModel, array $arguments)
    {
        return DSL::useTrait($arguments[0], $arguments[1] ?? null, $testCaseModel);
    }
});
