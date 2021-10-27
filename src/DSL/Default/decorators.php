<?php

use ThenLabs\PyramidalTests\DSL\DSL;
use ThenLabs\PyramidalTests\Model\Decorator\AbstractDecorator;
use ThenLabs\PyramidalTests\Model\Decorator\DecoratorsRegistry;
use ThenLabs\PyramidalTests\Model\TestCaseModel;

DecoratorsRegistry::registerGlobal('testCase', new class extends AbstractDecorator {
    public function decorate(TestCaseModel $testCaseModel, array $arguments)
    {
        return DSL::testCase($arguments[0] ?? '', $arguments[1] ?? null, true, $testCaseModel);
    }
});

DecoratorsRegistry::registerGlobal('endTestCase', new class extends AbstractDecorator {
    public function decorate(TestCaseModel $testCaseModel, array $arguments)
    {
        return $testCaseModel->getParent();
    }
});

DecoratorsRegistry::registerGlobal('setUpBeforeClass', new class extends AbstractDecorator {
    public function decorate(TestCaseModel $testCaseModel, array $arguments)
    {
        return DSL::setUpBeforeClass($arguments[0], $arguments[1] ?? true, $testCaseModel);
    }
});

DecoratorsRegistry::registerGlobal('setUpBeforeClassOnce', new class extends AbstractDecorator {
    public function decorate(TestCaseModel $testCaseModel, array $arguments)
    {
        return DSL::setUpBeforeClassOnce($arguments[0], $arguments[1] ?? true, $testCaseModel);
    }
});

DecoratorsRegistry::registerGlobal('setUp', new class extends AbstractDecorator {
    public function decorate(TestCaseModel $testCaseModel, array $arguments)
    {
        return DSL::setUp($arguments[0], $arguments[1] ?? true, $testCaseModel);
    }
});

DecoratorsRegistry::registerGlobal('test', new class extends AbstractDecorator {
    public function decorate(TestCaseModel $testCaseModel, array $arguments)
    {
        return DSL::test($arguments[0], $arguments[1] ?? null, $testCaseModel);
    }
});

DecoratorsRegistry::registerGlobal('tearDown', new class extends AbstractDecorator {
    public function decorate(TestCaseModel $testCaseModel, array $arguments)
    {
        return DSL::tearDown($arguments[0], $arguments[1] ?? true, $testCaseModel);
    }
});

DecoratorsRegistry::registerGlobal('tearDownAfterClass', new class extends AbstractDecorator {
    public function decorate(TestCaseModel $testCaseModel, array $arguments)
    {
        return DSL::tearDownAfterClass($arguments[0], $arguments[1] ?? true, $testCaseModel);
    }
});

DecoratorsRegistry::registerGlobal('tearDownAfterClassOnce', new class extends AbstractDecorator {
    public function decorate(TestCaseModel $testCaseModel, array $arguments)
    {
        return DSL::tearDownAfterClassOnce($arguments[0], $arguments[1] ?? true, $testCaseModel);
    }
});

DecoratorsRegistry::registerGlobal('staticProperty', new class extends AbstractDecorator {
    public function decorate(TestCaseModel $testCaseModel, array $arguments)
    {
        return DSL::staticProperty($arguments[0], $arguments[1] ?? null, $testCaseModel);
    }
});

DecoratorsRegistry::registerGlobal('property', new class extends AbstractDecorator {
    public function decorate(TestCaseModel $testCaseModel, array $arguments)
    {
        return DSL::property($arguments[0], $arguments[1] ?? null, $testCaseModel);
    }
});

DecoratorsRegistry::registerGlobal('staticMethod', new class extends AbstractDecorator {
    public function decorate(TestCaseModel $testCaseModel, array $arguments)
    {
        return DSL::staticMethod($arguments[0], $arguments[1], $testCaseModel);
    }
});

DecoratorsRegistry::registerGlobal('method', new class extends AbstractDecorator {
    public function decorate(TestCaseModel $testCaseModel, array $arguments)
    {
        return DSL::method($arguments[0], $arguments[1], $testCaseModel);
    }
});

DecoratorsRegistry::registerGlobal('useMacro', new class extends AbstractDecorator {
    public function decorate(TestCaseModel $testCaseModel, array $arguments)
    {
        return DSL::useMacro($arguments[0], $arguments[1] ?? null, $testCaseModel);
    }
});

DecoratorsRegistry::registerGlobal('useTrait', new class extends AbstractDecorator {
    public function decorate(TestCaseModel $testCaseModel, array $arguments)
    {
        return DSL::useTrait($arguments[0], $arguments[1] ?? null, $testCaseModel);
    }
});