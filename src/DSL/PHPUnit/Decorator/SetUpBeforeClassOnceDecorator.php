<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\DSL\PHPUnit\Decorator;

use ThenLabs\PyramidalTests\Decorator\AbstractDecorator;
use ThenLabs\PyramidalTests\DSL\DSL;
use ThenLabs\PyramidalTests\Model\TestCaseModel;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class SetUpBeforeClassOnceDecorator extends AbstractDecorator
{
    public function applyTo(TestCaseModel $testCaseModel, array $arguments)
    {
        return DSL::setUpBeforeClassOnce($arguments[0], $arguments[1] ?? true, $testCaseModel);
    }
}
