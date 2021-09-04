<?php
declare(strict_types=1);

use ThenLabs\PyramidalTests\DSL;
use ThenLabs\ClassBuilder\Model\Method;
use ThenLabs\ClassBuilder\Model\Property;
use ThenLabs\PyramidalTests\Model\TestModel;
use ThenLabs\PyramidalTests\Model\TestCaseModel;

/**
 * @param  string|Closure
 * @param  Closure|null
 */
function testCase($title, Closure $closure = null): TestCaseModel
{
    if ($title instanceof Closure) {
        $closure = $title;
        $title = '';
    }

    return DSL::testCase($title, $closure);
}

function setUpBeforeClass(Closure $closure, bool $invokeParents = true): void
{
    DSL::setUpBeforeClass($closure, $invokeParents);
}

function setUp(Closure $closure, bool $invokeParents = true): void
{
    DSL::setUp($closure, $invokeParents);
}

/**
 * @param  string|Closure
 * @param  Closure|null
 */
function test($title, Closure $closure = null): TestModel
{
    if ($title instanceof Closure) {
        $closure = $title;
        $title = '';
    }

    return DSL::test($title, $closure);
}

function tearDown(Closure $closure, bool $invokeParents = true): void
{
    DSL::tearDown($closure, $invokeParents);
}

function tearDownAfterClass(Closure $closure, bool $invokeParents = true): void
{
    DSL::tearDownAfterClass($closure, $invokeParents);
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

function staticProperty(string $name, $value = null): Property
{
    return DSL::staticProperty($name, $value);
}

function property(string $name, $value = null): Property
{
    return DSL::property($name, $value);
}

function staticMethod(string $name, Closure $closure): Method
{
    return DSL::staticMethod($name, $closure);
}

function method(string $name, Closure $closure): Method
{
    return DSL::method($name, $closure);
}

function useTrait(string $trait, array $definitions = []): void
{
    DSL::useTrait($trait, $definitions);
}
