<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Decorator;

use Closure;
use ThenLabs\PyramidalTests\Model\TestCaseModel;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 * @abstract
 */
abstract class AbstractDecorator
{
    public function applyTo(TestCaseModel $testCaseModel, array $arguments)
    {
    }

    public function getClosure(array $arguments): ?Closure
    {
        return null;
    }
}
