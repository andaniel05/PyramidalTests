<?php
declare(strict_types=1);

use ThenLabs\PyramidalTests\Decorator\DecoratorsRegistry;
use ThenLabs\PyramidalTests\DSL\DSL;
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
use ThenLabs\PyramidalTests\Model\TestCaseModel;
use ThenLabs\PyramidalTests\Model\TestModel;
use ThenLabs\PyramidalTests\Utils\Proxy;

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

function testCase($firstArgument = '', Closure $secondArgument = null): TestCaseModel
{
    return DSL::testCase($firstArgument, $secondArgument);
}

function setUpBeforeClass(Closure $closure, bool $invokeParents = true): void
{
    DSL::setUpBeforeClass($closure, $invokeParents);
}

function setUpBeforeClassOnce(Closure $closure, bool $invokeParents = true): void
{
    DSL::setUpBeforeClassOnce($closure, $invokeParents);
}

function setUp(Closure $closure, bool $invokeParents = true): void
{
    DSL::setUp($closure, $invokeParents);
}

/**
 * @param string|Closure
 * @param Closure|null
 */
function test($firstArgument, Closure $secondArgument = null): TestModel
{
    return DSL::test($firstArgument, $secondArgument);
}

function tearDown(Closure $closure, bool $invokeParents = true): void
{
    DSL::tearDown($closure, $invokeParents);
}

function tearDownAfterClass(Closure $closure, bool $invokeParents = true): void
{
    DSL::tearDownAfterClass($closure, $invokeParents);
}

function tearDownAfterClassOnce(Closure $closure, bool $invokeParents = true): void
{
    DSL::tearDownAfterClassOnce($closure, $invokeParents);
}

function setTestCaseClass(string $testCaseClass): void
{
    DSL::setTestCaseClass($testCaseClass);
}

function macro(string $title, Closure $closure): void
{
    DSL::macro($title, $closure);
}

function useMacro(string $title): void
{
    DSL::useMacro($title);
}

function useAndExtendMacro(string $title, Closure $closure): void
{
    DSL::useMacro($title, $closure);
}

function staticProperty(string $name, $value = null): Proxy
{
    return DSL::staticProperty($name, $value);
}

function property(string $name, $value = null): Proxy
{
    return DSL::property($name, $value);
}

function staticMethod(string $name, Closure $closure): Proxy
{
    return DSL::staticMethod($name, $closure);
}

function method(string $name, Closure $closure): Proxy
{
    return DSL::method($name, $closure);
}

function useTrait(string $trait, array $definitions = []): void
{
    DSL::useTrait($trait, $definitions);
}
