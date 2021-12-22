<?php

use ThenLabs\PyramidalTests\Decorator\DecoratorsRegistry;
use ThenLabs\PyramidalTests\DSL\PHPUnit\Decorator\EndTestCaseDecorator;
use ThenLabs\PyramidalTests\DSL\PHPUnit\Decorator\MethodDecorator;
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
use ThenLabs\PyramidalTests\DSL\PHPUnit\Decorator\UseMacroDecorator;
use ThenLabs\PyramidalTests\DSL\PHPUnit\Decorator\UseTraitDecorator;

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
DecoratorsRegistry::registerGlobal('method', new MethodDecorator());
DecoratorsRegistry::registerGlobal('useMacro', new UseMacroDecorator());
DecoratorsRegistry::registerGlobal('useTrait', new UseTraitDecorator());
