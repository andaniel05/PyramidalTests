<?php
declare(strict_types=1);

use ThenLabs\PyramidalTests\Decorator\DecoratorsRegistry;
use ThenLabs\PyramidalTests\DSL\Decorator\EndTestCaseDecorator;
use ThenLabs\PyramidalTests\DSL\Decorator\SetUpBeforeClassDecorator;
use ThenLabs\PyramidalTests\DSL\Decorator\SetUpBeforeClassOnceDecorator;
use ThenLabs\PyramidalTests\DSL\Decorator\SetUpDecorator;
use ThenLabs\PyramidalTests\DSL\Decorator\TearDownAfterClassDecorator;
use ThenLabs\PyramidalTests\DSL\Decorator\TearDownAfterClassOnceDecorator;
use ThenLabs\PyramidalTests\DSL\Decorator\TearDownDecorator;
use ThenLabs\PyramidalTests\DSL\Decorator\TestCaseDecorator;
use ThenLabs\PyramidalTests\DSL\Decorator\TestDecorator;
use ThenLabs\PyramidalTests\DSL\DSL;
use ThenLabs\PyramidalTests\Model\TestCaseModel;
use ThenLabs\PyramidalTests\Model\TestModel;

require_once __DIR__.'/Common.php';

DecoratorsRegistry::registerGlobal('testCase', new TestCaseDecorator());
DecoratorsRegistry::registerGlobal('endTestCase', new EndTestCaseDecorator());
DecoratorsRegistry::registerGlobal('setUpBeforeClass', new SetUpBeforeClassDecorator());
DecoratorsRegistry::registerGlobal('setUpBeforeClassOnce', new SetUpBeforeClassOnceDecorator());
DecoratorsRegistry::registerGlobal('setUp', new SetUpDecorator());
DecoratorsRegistry::registerGlobal('test', new TestDecorator());
DecoratorsRegistry::registerGlobal('tearDown', new TearDownDecorator());
DecoratorsRegistry::registerGlobal('tearDownAfterClass', new TearDownAfterClassDecorator());
DecoratorsRegistry::registerGlobal('tearDownAfterClassOnce', new TearDownAfterClassOnceDecorator());

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
function test($firstArgument = null, Closure $secondArgument = null): TestModel
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
