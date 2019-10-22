<?php
declare(strict_types=1);

/*
    Copyright (C) <2018>  <Andy Daniel Navarro Taño>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

use Andaniel05\PyramidalTests\DSL\DSL;

function setTestCaseNamespace(string $namespace): void
{
    DSL::setTestCaseNamespace($namespace);
}

function setTestCaseClass(string $class): void
{
    DSL::setTestCaseClass($class);
}

function testCase($description, Closure $closure = null): void
{
    DSL::testCase($description, $closure);
}

function setUpBeforeClass(Closure $closure, bool $invokeParent = true): void
{
    DSL::setUpBeforeClass($closure, $invokeParent);
}

function setUp(Closure $closure, bool $invokeParent = true): void
{
    DSL::setUp($closure, $invokeParent);
}

function test($description, ?Closure $closure = null): void
{
    DSL::test($description, $closure);
}

function tearDown(Closure $closure, bool $invokeParent = true): void
{
    DSL::tearDown($closure, $invokeParent);
}

function tearDownAfterClass(Closure $closure, bool $invokeParent = true): void
{
    DSL::tearDownAfterClass($closure, $invokeParent);
}

function createMethod(string $method, Closure $closure): void
{
    DSL::createMethod($method, $closure);
}

function createStaticMethod(string $method, Closure $closure): void
{
    DSL::createStaticMethod($method, $closure);
}

function createMacro(string $description, Closure $closure): void
{
    DSL::createMacro($description, $closure);
}

function useMacro(string $description, ...$args): void
{
    DSL::useMacro($description, $args);
}

function setUpBeforeClassOnce(Closure $closure, bool $invokeParent = true): void
{
    DSL::setUpBeforeClassOnce($closure, $invokeParent);
}

function tearDownAfterClassOnce(Closure $closure, bool $invokeParent = true): void
{
    DSL::tearDownAfterClassOnce($closure, $invokeParent);
}

function testIncomplete(string $description): void
{
    DSL::testIncomplete($description);
}
