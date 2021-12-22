<?php

use ThenLabs\PyramidalTests\Decorator\AbstractDecorator;
use ThenLabs\PyramidalTests\Decorator\DecoratorsRegistry;
use ThenLabs\PyramidalTests\DSL\DSL;
use ThenLabs\PyramidalTests\DSL\PHPUnit\Decorator\EndTestCaseDecorator;
use ThenLabs\PyramidalTests\DSL\PHPUnit\Decorator\SetUpBeforeClassDecorator;
use ThenLabs\PyramidalTests\DSL\PHPUnit\Decorator\SetUpBeforeClassOnceDecorator;
use ThenLabs\PyramidalTests\DSL\PHPUnit\Decorator\SetUpDecorator;
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

DecoratorsRegistry::registerGlobal('staticProperty', new class extends AbstractDecorator {
    public function applyTo(TestCaseModel $testCaseModel, array $arguments)
    {
        return DSL::staticProperty($arguments[0], $arguments[1] ?? null, $testCaseModel);
    }
});

DecoratorsRegistry::registerGlobal('property', new class extends AbstractDecorator {
    public function applyTo(TestCaseModel $testCaseModel, array $arguments)
    {
        return DSL::property($arguments[0], $arguments[1] ?? null, $testCaseModel);
    }
});

DecoratorsRegistry::registerGlobal('staticMethod', new class extends AbstractDecorator {
    public function applyTo(TestCaseModel $testCaseModel, array $arguments)
    {
        return DSL::staticMethod($arguments[0], $arguments[1], $testCaseModel);
    }
});

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
