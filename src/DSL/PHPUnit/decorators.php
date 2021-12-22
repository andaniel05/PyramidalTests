<?php

use ThenLabs\PyramidalTests\Decorator\AbstractDecorator;
use ThenLabs\PyramidalTests\Decorator\DecoratorsRegistry;
use ThenLabs\PyramidalTests\DSL\DSL;
use ThenLabs\PyramidalTests\DSL\PHPUnit\Decorator\EndTestCaseDecorator;
use ThenLabs\PyramidalTests\DSL\PHPUnit\Decorator\SetUpBeforeClassDecorator;
use ThenLabs\PyramidalTests\DSL\PHPUnit\Decorator\SetUpBeforeClassOnceDecorator;
use ThenLabs\PyramidalTests\DSL\PHPUnit\Decorator\TestCaseDecorator;
use ThenLabs\PyramidalTests\Model\TestCaseModel;

DecoratorsRegistry::registerGlobal('testCase', new TestCaseDecorator());
DecoratorsRegistry::registerGlobal('endTestCase', new EndTestCaseDecorator());
DecoratorsRegistry::registerGlobal('setUpBeforeClass', new SetUpBeforeClassDecorator());
DecoratorsRegistry::registerGlobal('setUpBeforeClassOnce', new SetUpBeforeClassOnceDecorator());

DecoratorsRegistry::registerGlobal('setUp', new class extends AbstractDecorator {
    public function applyTo(TestCaseModel $testCaseModel, array $arguments)
    {
        return DSL::setUp($arguments[0], $arguments[1] ?? true, $testCaseModel);
    }
});

DecoratorsRegistry::registerGlobal('test', new class extends AbstractDecorator {
    public function applyTo(TestCaseModel $testCaseModel, array $arguments)
    {
        return DSL::test($arguments[0], $arguments[1] ?? null, $testCaseModel);
    }
});

DecoratorsRegistry::registerGlobal('tearDown', new class extends AbstractDecorator {
    public function applyTo(TestCaseModel $testCaseModel, array $arguments)
    {
        return DSL::tearDown($arguments[0], $arguments[1] ?? true, $testCaseModel);
    }
});

DecoratorsRegistry::registerGlobal('tearDownAfterClass', new class extends AbstractDecorator {
    public function applyTo(TestCaseModel $testCaseModel, array $arguments)
    {
        return DSL::tearDownAfterClass($arguments[0], $arguments[1] ?? true, $testCaseModel);
    }
});

DecoratorsRegistry::registerGlobal('tearDownAfterClassOnce', new class extends AbstractDecorator {
    public function applyTo(TestCaseModel $testCaseModel, array $arguments)
    {
        return DSL::tearDownAfterClassOnce($arguments[0], $arguments[1] ?? true, $testCaseModel);
    }
});

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
