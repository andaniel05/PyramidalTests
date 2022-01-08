<?php
declare(strict_types=1);

use ThenLabs\PyramidalTests\Decorator\DecoratorsRegistry;
use ThenLabs\PyramidalTests\DSL\Decorator\MethodDecorator;
use ThenLabs\PyramidalTests\DSL\Decorator\PropertyDecorator;
use ThenLabs\PyramidalTests\DSL\Decorator\StaticMethodDecorator;
use ThenLabs\PyramidalTests\DSL\Decorator\StaticPropertyDecorator;
use ThenLabs\PyramidalTests\DSL\Decorator\UseMacroDecorator;
use ThenLabs\PyramidalTests\DSL\Decorator\UseTraitDecorator;
use ThenLabs\PyramidalTests\DSL\DSL;
use ThenLabs\PyramidalTests\Utils\Proxy;

DecoratorsRegistry::registerGlobal('staticProperty', new StaticPropertyDecorator());
DecoratorsRegistry::registerGlobal('property', new PropertyDecorator());
DecoratorsRegistry::registerGlobal('staticMethod', new StaticMethodDecorator());
DecoratorsRegistry::registerGlobal('method', new MethodDecorator());
DecoratorsRegistry::registerGlobal('useMacro', new UseMacroDecorator());
DecoratorsRegistry::registerGlobal('useTrait', new UseTraitDecorator());

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
