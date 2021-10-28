<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Model\Decorator;

use Closure;
use ThenLabs\PyramidalTests\Model\TestCaseModel;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 * @abstract
 */
abstract class AbstractDecorator
{
    public function getClosure(): ?Closure
    {
        return null;
    }

    public function applyTo(TestCaseModel $testCaseModel, array $arguments)
    {
    }
}
