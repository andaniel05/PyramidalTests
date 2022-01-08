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

DecoratorsRegistry::registerGlobal('describe', new TestCaseDecorator());
DecoratorsRegistry::registerGlobal('end', new EndTestCaseDecorator());
DecoratorsRegistry::registerGlobal('beforeAll', new SetUpBeforeClassDecorator());
DecoratorsRegistry::registerGlobal('beforeAllOnce', new SetUpBeforeClassOnceDecorator());
DecoratorsRegistry::registerGlobal('beforeEach', new SetUpDecorator());
DecoratorsRegistry::registerGlobal('it', new TestDecorator());
DecoratorsRegistry::registerGlobal('afterEach', new TearDownDecorator());
DecoratorsRegistry::registerGlobal('afterAll', new TearDownAfterClassDecorator());
DecoratorsRegistry::registerGlobal('afterAllOnce', new TearDownAfterClassOnceDecorator());

function describe($firstArgument = '', Closure $secondArgument = null): TestCaseModel
{
    return DSL::testCase($firstArgument, $secondArgument);
}

function beforeAll(Closure $closure, bool $invokeParents = true): void
{
    DSL::setUpBeforeClass($closure, $invokeParents);
}

function beforeAllOnce(Closure $closure, bool $invokeParents = true): void
{
    DSL::setUpBeforeClassOnce($closure, $invokeParents);
}

function beforeEach(Closure $closure, bool $invokeParents = true): void
{
    DSL::setUp($closure, $invokeParents);
}

/**
 * @param string|Closure
 * @param Closure|null
 */
function it($firstArgument, Closure $secondArgument = null): TestModel
{
    return DSL::test($firstArgument, $secondArgument);
}

function afterEach(Closure $closure, bool $invokeParents = true): void
{
    DSL::tearDown($closure, $invokeParents);
}

function afterAll(Closure $closure, bool $invokeParents = true): void
{
    DSL::tearDownAfterClass($closure, $invokeParents);
}

function afterAllOnce(Closure $closure, bool $invokeParents = true): void
{
    DSL::tearDownAfterClassOnce($closure, $invokeParents);
}
