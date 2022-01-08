<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\DSL\TDD\Decorator;

use ThenLabs\PyramidalTests\Decorator\AbstractDecorator;
use ThenLabs\PyramidalTests\DSL\DSL;
use ThenLabs\PyramidalTests\Model\TestCaseModel;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class SetUpDecorator extends AbstractDecorator
{
    public function applyTo(TestCaseModel $testCaseModel, array $arguments)
    {
        return DSL::setUp($arguments[0], $arguments[1] ?? true, $testCaseModel);
    }
}
